<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ShippingSetting;
use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        $cart = $request->user()->cart()
            ->with('items.product.images', 'items.variant', 'items.selectedDesign')
            ->firstOrCreate([]);
        $cartItems = $cart->items;
        $selectedIds = $request->validate([
            'items' => ['sometimes', 'array', 'min:1'],
            'items.*' => ['required', 'integer', 'distinct'],
        ])['items'] ?? null;
        if ($selectedIds !== null) {
            $selectedIds = collect($selectedIds)->map(fn ($id) => (int) $id)->unique()->values();
            $cartItems = $cartItems->whereIn('id', $selectedIds);
            if ($cartItems->count() !== $selectedIds->count()) {
                return redirect()->route('cart')->withErrors(['cart' => 'One or more selected products are no longer in your cart.']);
            }
        } else {
            $selectedIds = $cartItems->pluck('id');
        }

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart')->withErrors(['cart' => 'Your cart is empty.']);
        }
        $cart->setRelation('items', $cartItems->values());

        return view('checkout.index', [
            'cart' => $cart,
            'selectedCartItemIds' => $selectedIds->all(),
            'addresses' => $request->user()->addresses()->latest()->get(),
            'shippingSettings' => ShippingSetting::current(),
            'siteSettings' => SiteSetting::current(),
            'prepaidMethods' => SiteSetting::current()->prepaidPaymentMethods(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:30'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'shipping_address' => ['required', 'string', 'max:2000'],
            'area' => ['nullable', 'string', 'max:150'],
            'city' => ['required', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'customer_note' => ['nullable', 'string', 'max:2000'],
            'coupon_code' => ['nullable', 'string', 'max:100'],
            'payment_method' => ['required', 'string', 'in:COD,Bank Transfer,JazzCash,EasyPaisa'],
            'cart_item_ids' => ['sometimes', 'array', 'min:1'],
            'cart_item_ids.*' => ['required', 'integer', 'distinct'],
        ]);

        $order = DB::transaction(function () use ($request, $data) {
            $shippingSettings = ShippingSetting::current();
            if (! $shippingSettings->nationwide) {
                throw ValidationException::withMessages(['shipping' => 'Online delivery is currently unavailable.']);
            }
            $prepaidMethods = SiteSetting::current()->prepaidPaymentMethods();
            if ($data['payment_method'] === 'COD' && ! $shippingSettings->cod_enabled) {
                throw ValidationException::withMessages(['payment_method' => 'Cash on Delivery is currently unavailable.']);
            }
            if ($data['payment_method'] !== 'COD' && ! array_key_exists($data['payment_method'], $prepaidMethods)) {
                throw ValidationException::withMessages(['payment_method' => 'This prepaid method is not currently configured.']);
            }

            $cart = $request->user()->cart()
                ->with('items.product', 'items.variant', 'items.selectedDesign')
                ->lockForUpdate()
                ->firstOrCreate([]);
            if ($cart->items->isEmpty()) {
                throw ValidationException::withMessages(['cart' => 'Your cart is empty.']);
            }
            $selectedIds = isset($data['cart_item_ids'])
                ? collect($data['cart_item_ids'])->map(fn ($id) => (int) $id)->unique()->values()
                : $cart->items->pluck('id');
            $items = $cart->items->whereIn('id', $selectedIds);
            if ($items->isEmpty() || $items->count() !== $selectedIds->count()) {
                throw ValidationException::withMessages(['cart' => 'One or more selected products are no longer in your cart.']);
            }

            $subtotal = 0;
            $lines = [];
            foreach ($items as $item) {
                $product = Product::where('status', 'active')->lockForUpdate()->find($item->product_id);
                $variant = $item->product_variant_id
                    ? ProductVariant::where('product_id', $item->product_id)->lockForUpdate()->find($item->product_variant_id)
                    : null;
                if (! $product || ($item->product_variant_id && ! $variant)) {
                    throw ValidationException::withMessages(['cart' => 'A product in your cart is no longer available.']);
                }
                if (($product->size_selection_enabled && ! $variant?->size)
                    || ($product->color_selection_enabled && ! $variant?->color)) {
                    throw ValidationException::withMessages([
                        'cart' => "{$product->name} is missing a required size or color selection. Remove it and add it again.",
                    ]);
                }
                $design = null;
                if ($product->design_selection_enabled) {
                    $design = $item->selectedDesign;
                    if (! $design || $design->product_id !== $product->id || ! $design->is_selectable_design) {
                        throw ValidationException::withMessages([
                            'cart' => "{$product->name} is missing a valid design selection. Remove it and add it again.",
                        ]);
                    }
                }

                $stock = $variant?->stock_quantity ?? $product->stock_quantity;
                if ($item->quantity > $stock) {
                    throw ValidationException::withMessages(['cart' => "{$product->name} does not have enough stock."]);
                }

                $unitPrice = (float) ($product->sale_price ?? $product->price) + (float) ($variant?->additional_price ?? 0);
                $lineTotal = $unitPrice * $item->quantity;
                $subtotal += $lineTotal;
                $lines[] = compact('item', 'product', 'variant', 'design', 'unitPrice', 'lineTotal');
            }

            $siteSettings = SiteSetting::current();
            if ($subtotal < $siteSettings->min_order) {
                throw ValidationException::withMessages([
                    'minimum_order' => 'The minimum order amount is Rs '.number_format($siteSettings->min_order).'.',
                ]);
            }

            $coupon = null;
            $discount = 0;
            if (! empty($data['coupon_code'])) {
                $coupon = Coupon::where('code', strtoupper(trim($data['coupon_code'])))
                    ->where('status', 'active')->lockForUpdate()->first();
                if (! $coupon || ($coupon->starts_at && $coupon->starts_at->isFuture())
                    || ($coupon->expires_at && $coupon->expires_at->isPast())
                    || ($coupon->usage_limit && $coupon->used_count >= $coupon->usage_limit)
                    || $subtotal < $coupon->minimum_order_amount) {
                    throw ValidationException::withMessages(['coupon_code' => 'This coupon is invalid or does not apply to your order.']);
                }
                $discount = $coupon->type === 'percentage'
                    ? $subtotal * (float) $coupon->value / 100
                    : (float) $coupon->value;
                $discount = min($subtotal, $discount, $coupon->maximum_discount ?? $discount);
            }

            $shippingFee = $shippingSettings->deliveryFee($subtotal, $data['city'])
                + ($data['payment_method'] === 'COD' ? $shippingSettings->cod_fee : 0);
            $order = Order::create([
                'user_id' => $request->user()->id,
                'coupon_id' => $coupon?->id,
                'order_number' => 'BW-'.now()->format('ymd').'-'.Str::upper(Str::random(8)),
                'customer_name' => $data['customer_name'],
                'customer_phone' => $data['customer_phone'],
                'customer_email' => $data['customer_email'] ?? $request->user()->email,
                'shipping_address' => trim(($data['area'] ?? '').' '.$data['shipping_address']),
                'city' => $data['city'],
                'postal_code' => $data['postal_code'] ?? null,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'shipping_fee' => $shippingFee,
                'total_amount' => $subtotal - $discount + $shippingFee,
                'payment_method' => $data['payment_method'],
                'payment_status' => 'pending',
                'order_status' => 'pending',
                'shipping_status' => 'pending',
                'customer_note' => $data['customer_note'] ?? null,
            ]);

            foreach ($lines as $line) {
                $order->items()->create([
                    'product_id' => $line['product']->id,
                    'product_variant_id' => $line['variant']?->id,
                    'product_name' => $line['product']->name,
                    'sku' => $line['product']->sku,
                    'size' => $line['variant']?->size,
                    'color' => $line['variant']?->color,
                    'design_name' => $line['design']?->design_name,
                    'design_image_path' => $line['design']?->image,
                    'quantity' => $line['item']->quantity,
                    'price' => $line['unitPrice'],
                    'total' => $line['lineTotal'],
                ]);
                if ($line['variant']) {
                    $line['variant']->decrement('stock_quantity', $line['item']->quantity);
                } else {
                    $line['product']->decrement('stock_quantity', $line['item']->quantity);
                }
            }

            $order->payments()->create([
                'payment_method' => $data['payment_method'],
                'amount' => $order->total_amount,
                'status' => 'pending',
            ]);

            if ($coupon) {
                CouponUsage::create([
                    'coupon_id' => $coupon->id,
                    'user_id' => $request->user()->id,
                    'order_id' => $order->id,
                    'discount_amount' => $discount,
                ]);
                $coupon->increment('used_count');
            }

            $userAddresses = $request->user()->addresses();
            $userAddresses->firstOrCreate(
                [
                    'phone' => $data['customer_phone'],
                    'city' => $data['city'],
                    'area' => $data['area'] ?? null,
                    'address' => $data['shipping_address'],
                ],
                [
                    'name' => $data['customer_name'],
                    'postal_code' => $data['postal_code'] ?? null,
                    'address_type' => 'home',
                    'is_default' => ! $userAddresses->exists(),
                ]
            );

            $cart->items()->whereIn('id', $selectedIds)->delete();

            return $order;
        });

        $message = $order->payment_method === 'COD'
            ? 'Your COD order has been placed.'
            : 'Your order is placed. Upload your payment receipt from the order details to complete payment verification.';

        return redirect()->route('orders.show', $order)->with('status', $message);
    }
}
