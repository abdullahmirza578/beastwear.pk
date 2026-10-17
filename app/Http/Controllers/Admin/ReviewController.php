<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ReviewController extends Controller
{
    public function show(): View
    {
        return view('admin.reviews.index');
    }

    public function index(): JsonResponse
    {
        $reviews = Review::with(['user', 'product'])
            ->latest()
            ->get()
            ->map(fn (Review $review) => [
                'id' => $review->id,
                'customer' => $review->user->name,
                'customerId' => $review->user_id,
                'product' => $review->product->name,
                'productId' => $review->product_id,
                'rating' => (int) $review->rating,
                'text' => $review->comment ?? '',
                'status' => ucfirst($review->status),
                'date' => $review->created_at?->toIso8601String(),
            ]);

        return response()->json($reviews);
    }

    public function update(Request $request, Review $review): JsonResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['Approved', 'Rejected'])],
        ]);

        $review->update(['status' => strtolower($data['status'])]);
        $this->clearStorefrontCatalog();

        return response()->json(['id' => $review->id, 'status' => ucfirst($review->status)]);
    }

    public function destroy(Review $review): JsonResponse
    {
        $review->delete();
        $this->clearStorefrontCatalog();

        return response()->json(['deleted' => true]);
    }

    private function clearStorefrontCatalog(): void
    {
        Cache::forget('storefront.catalog.v3');
    }
}
