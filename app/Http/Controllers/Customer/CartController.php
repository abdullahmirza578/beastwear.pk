<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ShippingSetting;
use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CartController extends Controller
{
    public function index(Request $request): View
    {
        $cart = $request->user()->cart()->firstOrCreate([]);

        return view('cart.index', [
            'cart' => $cart->load('items.product.category', 'items.product.images', 'items.variant', 'items.selectedDesign'),
            'subtotal' => $cart->items->sum(fn (CartItem $item) => (float) $item->price * $item->quantity),
            'shippingSettings' => ShippingSetting::current(),
            'siteSettings' => SiteSetting::current(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'size' => ['nullable', 'string', 'max:20'],
            'color' => ['nullable', 'string', 'max:50'],
            'design_image_id' => ['nullable', 'integer'],
            'quantity' => ['required', 'integer', 'min:1', 'max:20'],
        ]);

        DB::transaction(function () use ($request, $data) {
            $product = Product::where('status', 'active')->lockForUpdate()->findOrFail($data['product_id']);
            $size = $product->size_selection_enabled ? trim($data['size'] ?? '') : null;
            $color = $product->color_selection_enabled ? trim($data['color'] ?? '') : null;
            $variant = null;

            if ($product->size_selection_enabled && $size === '') {
                throw ValidationException::withMessages(['size' => 'Please select a size.']);
            }
            if ($product->color_selection_enabled && $color === '') {
                throw ValidationException::withMessages(['color' => 'Please select a color.']);
            }
            if ($product->size_selection_enabled || $product->color_selection_enabled) {
                $variantQuery = ProductVariant::where('product_id', $product->id)
                    ->where('stock_quantity', '>', 0)
                    ->lockForUpdate();
                if ($product->size_selection_enabled) {
                    $variantQuery->where('size', $size);
                }
                if ($product->color_selection_enabled) {
                    $variantQuery->where('color', $color);
                }
                $variant = $variantQuery->first();
                if (! $variant) {
                    throw ValidationException::withMessages([
                        'options' => 'That combination is unavailable. Please choose another option.',
                    ]);
                }
            }

            $design = null;
            if ($product->design_selection_enabled) {
                if (empty($data['design_image_id'])) {
                    throw ValidationException::withMessages(['design_image_id' => 'Please select a design.']);
                }
                $design = $product->images()
                    ->where('is_selectable_design', true)
                    ->whereKey($data['design_image_id'])
                    ->first();
                if (! $design) {
                    throw ValidationException::withMessages([
                        'design_image_id' => 'Please select one of this product’s available designs.',
                    ]);
                }
            }

            $available = $variant?->stock_quantity ?? $product->stock_quantity;
            if ($data['quantity'] > $available) {
                throw ValidationException::withMessages(['quantity' => 'The requested quantity is not available.']);
            }

            $cart = $request->user()->cart()->firstOrCreate([]);
            $selectionKey = 'v'.($variant?->id ?? 0).'-d'.($design?->id ?? 0);
            $item = $cart->items()->where('selection_key', $selectionKey)
                ->lockForUpdate()
                ->first();

            $quantity = ($item?->quantity ?? 0) + $data['quantity'];
            if ($quantity > $available) {
                throw ValidationException::withMessages(['quantity' => 'The cart quantity exceeds available stock.']);
            }

            $price = (float) ($product->sale_price ?? $product->price)
                + (float) ($variant?->additional_price ?? 0);
            if ($item) {
                $item->update(['quantity' => $quantity, 'price' => $price]);
            } else {
                $cart->items()->create([
                    'product_id' => $product->id,
                    'product_variant_id' => $variant?->id,
                    'selected_design_image_id' => $design?->id,
                    'selection_key' => $selectionKey,
                    'quantity' => $data['quantity'],
                    'price' => $price,
                ]);
            }
        });

        return back()->with('status', 'Product added to your cart.');
    }

    public function update(Request $request, CartItem $cartItem): RedirectResponse
    {
        $data = $request->validate(['quantity' => ['required', 'integer', 'min:1', 'max:20']]);
        abort_unless($cartItem->cart->user_id === $request->user()->id, 404);

        $stock = $cartItem->variant?->stock_quantity ?? $cartItem->product->stock_quantity;
        if ($data['quantity'] > $stock) {
            throw ValidationException::withMessages(['quantity' => 'The requested quantity is not available.']);
        }

        $cartItem->update($data);

        return back()->with('status', 'Cart updated.');
    }

    public function destroy(Request $request, CartItem $cartItem): RedirectResponse
    {
        abort_unless($cartItem->cart->user_id === $request->user()->id, 404);
        $cartItem->delete();

        return back()->with('status', 'Product removed from your cart.');
    }

    public function destroySelected(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'selected_items' => ['required', 'array', 'min:1'],
            'selected_items.*' => ['required', 'integer', 'distinct'],
        ]);
        $cart = $request->user()->cart()->firstOrCreate([]);
        $selectedIds = collect($data['selected_items'])->map(fn ($id) => (int) $id)->unique()->values();
        $ownedItems = $cart->items()->whereIn('id', $selectedIds)->get();

        if ($ownedItems->count() !== $selectedIds->count()) {
            throw ValidationException::withMessages(['selected_items' => 'One or more selected products are no longer in your cart.']);
        }

        $ownedItems->each->delete();

        return back()->with('status', 'Selected products removed from your cart.');
    }

    public function clear(Request $request): RedirectResponse
    {
        $request->user()->cart()->firstOrCreate([])->items()->delete();

        return back()->with('status', 'Cart cleared.');
    }
}
