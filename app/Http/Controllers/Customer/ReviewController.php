<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ReviewController extends Controller
{
    public function store(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate([
            'order_id' => ['required', 'integer', 'exists:orders,id'],
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string', 'max:3000'],
        ]);

        $order = Order::where('user_id', $request->user()->id)
            ->where('order_status', 'delivered')
            ->whereKey($data['order_id'])
            ->whereHas('items', fn ($query) => $query->where('product_id', $product->id))
            ->first();
        if (! $order) {
            throw ValidationException::withMessages(['order_id' => 'A delivered order containing this product is required to review it.']);
        }

        $product->reviews()->updateOrCreate(
            ['user_id' => $request->user()->id, 'order_id' => $order->id],
            ['rating' => $data['rating'], 'comment' => $data['comment'] ?? null, 'status' => 'pending']
        );

        return back()->with('status', 'Your review was submitted for moderation.');
    }
}
