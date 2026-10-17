<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WishlistController extends Controller
{
    public function index(Request $request): View
    {
        $wishlist = $request->user()->wishlist()->firstOrCreate([]);

        return view('account.wishlist', [
            'products' => Product::with(['category', 'images', 'variants'])
                ->whereHas('wishlistItems', fn ($query) => $query->where('wishlist_id', $wishlist->id))
                ->where('status', 'active')->paginate(12),
        ]);
    }

    public function store(Request $request, Product $product): RedirectResponse
    {
        abort_unless($product->status === 'active', 404);
        $request->user()->wishlist()->firstOrCreate([])->items()->firstOrCreate(['product_id' => $product->id]);

        return back()->with('status', 'Product added to your wishlist.');
    }

    public function destroy(Request $request, Product $product): RedirectResponse
    {
        $wishlist = $request->user()->wishlist()->firstOrCreate([]);
        $wishlist->items()->where('product_id', $product->id)->delete();

        return back()->with('status', 'Product removed from your wishlist.');
    }
}
