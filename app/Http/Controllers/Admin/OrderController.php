<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function index(): JsonResponse
    {
        $orders = Order::with(['items.product.images', 'coupon'])
            ->latest()
            ->get()
            ->map(fn (Order $order) => $this->serialize($order));

        return response()->json($orders);
    }

    public function show(string $orderKey): JsonResponse
    {
        return response()->json($this->serialize($this->findOrder($orderKey)));
    }

    public function update(Request $request, string $orderKey): JsonResponse
    {
        $data = $request->validate([
            'status' => ['sometimes', 'required', 'string', 'in:Pending,Confirmed,Processing,Packed,Shipped,Out for Delivery,Delivered,Cancelled,Returned,Exchange Requested'],
            'paymentStatus' => ['sometimes', 'required', 'string', 'in:Unpaid,Paid,Refunded'],
            'tracking' => ['sometimes', 'nullable', 'string', 'max:100'],
            'courier' => ['sometimes', 'nullable', 'string', 'max:100'],
            'adminNotes' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'customerNotes' => ['sometimes', 'nullable', 'string', 'max:2000'],
        ]);

        $order = $this->findOrder($orderKey);
        $changes = [];

        if (array_key_exists('status', $data)) {
            $changes['order_status'] = Str::snake($data['status']);
            $changes['shipping_status'] = match ($data['status']) {
                'Pending' => 'pending',
                'Confirmed' => 'confirmed',
                'Processing' => 'processing',
                'Packed' => 'packed',
                'Shipped' => 'shipped',
                'Out for Delivery' => 'out_for_delivery',
                'Delivered' => 'delivered',
                'Cancelled' => 'cancelled',
                'Returned' => 'returned',
                'Exchange Requested' => 'exchange_requested',
            };
        }
        if (array_key_exists('paymentStatus', $data)) {
            $changes['payment_status'] = match ($data['paymentStatus']) {
                'Paid' => 'paid',
                'Refunded' => 'refunded',
                default => 'pending',
            };
        }
        if (array_key_exists('tracking', $data)) {
            $changes['tracking_number'] = $data['tracking'];
        }
        if (array_key_exists('courier', $data)) {
            $changes['shipping_courier'] = $data['courier'];
        }
        if (array_key_exists('adminNotes', $data)) {
            $changes['admin_note'] = $data['adminNotes'];
        }
        if (array_key_exists('customerNotes', $data)) {
            $changes['customer_note'] = $data['customerNotes'];
        }

        $order->update($changes);

        if (array_key_exists('paymentStatus', $data)) {
            $paymentStatus = $changes['payment_status'];
            $order->payments()->latest()->first()?->update([
                'status' => $paymentStatus,
                'paid_at' => $paymentStatus === 'paid' ? now() : null,
            ]);
        }

        return response()->json($this->serialize($order->fresh()->load(['items.product.images', 'coupon'])));
    }

    private function findOrder(string $orderKey): Order
    {
        $order = Order::with(['items.product.images', 'coupon'])
            ->where('order_number', $orderKey)
            ->first();

        if ($order) {
            return $order;
        }

        abort_unless(ctype_digit($orderKey), 404);

        return Order::with(['items.product.images', 'coupon'])->findOrFail($orderKey);
    }

    private function serialize(Order $order): array
    {
        return [
            'id' => $order->order_number,
            'publicTrackingId' => $order->public_tracking_id,
            'customerId' => $order->user_id,
            'customer' => $order->customer_name,
            'phone' => $order->customer_phone,
            'email' => $order->customer_email,
            'date' => $order->created_at?->toIso8601String(),
            'address' => [
                'province' => '',
                'city' => $order->city,
                'area' => '',
                'address' => $order->shipping_address,
            ],
            'items' => $order->items->map(fn ($item) => [
                'productId' => $item->product_id,
                'image' => $item->product?->images->first()?->url ?? '',
                'name' => $item->product_name,
                'sku' => $item->sku ?? '',
                'size' => $item->size ?? '—',
                'color' => $item->color ?? '—',
                'designName' => $item->design_name,
                'designImage' => $item->design_image_path
                    ? \App\Models\ProductImage::urlFor($item->design_image_path)
                    : null,
                'qty' => $item->quantity,
                'price' => (float) $item->price,
                'total' => (float) $item->total,
            ])->values(),
            'coupon' => $order->coupon?->code,
            'subtotal' => (float) $order->subtotal,
            'discount' => (float) $order->discount,
            'delivery' => (float) $order->shipping_fee,
            'total' => (float) $order->total_amount,
            'payment' => $order->payment_method === 'COD' ? 'Cash on Delivery' : $order->payment_method,
            'paymentStatus' => match ($order->payment_status) {
                'paid' => 'Paid',
                'refunded' => 'Refunded',
                'rejected' => 'Rejected',
                default => 'Unpaid',
            },
            'status' => match ($order->order_status) {
                'out_for_delivery' => 'Out for Delivery',
                'exchange_requested' => 'Exchange Requested',
                default => Str::headline($order->order_status),
            },
            'tracking' => $order->tracking_number,
            'courier' => $order->shipping_courier,
            'adminNotes' => $order->admin_note,
            'customerNotes' => $order->customer_note,
            'updatedAt' => $order->updated_at?->toIso8601String(),
        ];
    }
}
