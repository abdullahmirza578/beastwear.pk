<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogController extends Controller
{
    public function home(): View
    {
        $eligibleBanners = Banner::query()
            ->where('status', 'active')
            ->where(fn ($query) => $query->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn ($query) => $query->whereNull('ends_at')->orWhere('ends_at', '>=', now()));

        return view('home.index', [
            'categories' => Category::where('status', 'active')->orderBy('name')->get(),
            'featuredProducts' => Product::with(['category', 'images', 'variants'])
                ->where('status', 'active')->where('featured', true)->latest()->take(8)->get(),
            'newArrivals' => Product::with(['category', 'images', 'variants'])
                ->where('status', 'active')->latest()->take(8)->get(),
            'bestSellers' => Product::with(['category', 'images', 'variants'])
                ->where('status', 'active')->orderByDesc('stock_quantity')->take(8)->get(),
            'heroBanner' => (clone $eligibleBanners)
                ->whereIn('position', ['home-hero', 'hero'])
                ->orderByRaw("CASE WHEN position = 'home-hero' THEN 0 ELSE 1 END")
                ->latest('id')
                ->first(),
            'promotionalBanners' => (clone $eligibleBanners)
                ->whereIn('position', ['Below Hero', 'Mid Page', 'Above Footer', 'Footer'])
                ->orderBy('position')
                ->orderBy('id')
                ->get(),
        ]);
    }

    public function shop(Request $request): View
    {
        $request->validate([
            'category' => ['nullable', 'string', 'max:100'],
            'q' => ['nullable', 'string', 'max:100'],
            'sale' => ['sometimes', 'boolean'],
            'sort' => ['nullable', 'in:featured,newest,popular,price-asc,price-desc,rating'],
        ]);

        $productQuery = Product::query()
            ->where('status', 'active')
            ->when($request->filled('category'), fn ($query) => $query->whereHas(
                'category',
                fn ($category) => $category->where('slug', $request->string('category'))
            ))
            ->when($request->filled('q'), fn ($query) => $query->where(function ($search) use ($request) {
                $term = '%'.$request->string('q').'%';
                $search->where('name', 'like', $term)
                    ->orWhere('sku', 'like', $term)
                    ->orWhereHas('category', fn ($category) => $category->where('name', 'like', $term));
            }))
            ->when($request->boolean('sale'), fn ($query) => $query
                ->whereNotNull('sale_price')->whereColumn('sale_price', '<', 'price'))
            ->when($request->string('sort')->value() === 'price-asc', fn ($query) => $query->orderByRaw('COALESCE(sale_price, price) asc'))
            ->when($request->string('sort')->value() === 'price-desc', fn ($query) => $query->orderByRaw('COALESCE(sale_price, price) desc'))
            ->when($request->string('sort')->value() === 'newest', fn ($query) => $query->latest())
            ->when(! in_array($request->string('sort')->value(), ['price-asc', 'price-desc', 'newest'], true), fn ($query) => $query
                ->orderByDesc('featured')->latest());

        return view('shop.index', [
            'shopProductIds' => $productQuery->pluck('id')->all(),
            'categories' => Category::where('status', 'active')->orderBy('name')->get(),
        ]);
    }

    public function search(Request $request): View
    {
        $data = $request->validate(['q' => ['nullable', 'string', 'max:100']]);
        $term = trim($data['q'] ?? '');
        $words = preg_split('/\s+/', $term, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        $products = Product::query()
            ->where('status', 'active')
            ->when($words !== [], function ($query) use ($words): void {
                foreach ($words as $word) {
                    $like = '%'.$word.'%';
                    $query->where(function ($matches) use ($like): void {
                        $matches->where('name', 'like', $like)
                            ->orWhere('sku', 'like', $like)
                            ->orWhereHas('category', fn ($category) => $category->where('name', 'like', $like));
                    });
                }
            })
            ->latest()
            ->pluck('id');

        return view('shop.search', [
            'searchTerm' => $term,
            'searchProductIds' => $products->all(),
            'searchResultCount' => $products->count(),
        ]);
    }

    public function category(Request $request): View
    {
        $request->validate(['cat' => ['nullable', 'string', 'max:100']]);
        $category = $request->filled('cat')
            ? Category::where('status', 'active')->where('slug', $request->query('cat'))->firstOrFail()
            : null;
        $categoryProducts = $category
            ? $category->products()->where('status', 'active')->latest()->pluck('products.id')
            : Product::where('status', 'active')->latest()->pluck('id');

        return view('shop.category', [
            'category' => $category,
            'categories' => Category::where('status', 'active')
                ->withCount(['products' => fn ($query) => $query->where('status', 'active')])
                ->orderBy('name')->get(),
            'categoryProductIds' => $categoryProducts->all(),
        ]);
    }

    public function product(string $slug): View
    {
        $product = Product::with([
            'category',
            'images',
            'variants' => fn ($query) => $query->where('stock_quantity', '>', 0),
            'reviews' => fn ($query) => $query->where('status', 'approved')->with('user')->latest(),
        ])->where('status', 'active')->where('slug', $slug)->firstOrFail();
        $reviewOrders = auth()->check()
            ? auth()->user()->orders()
                ->where('order_status', 'delivered')
                ->whereHas('items', fn ($query) => $query->where('product_id', $product->id))
                ->with(['items' => fn ($query) => $query->where('product_id', $product->id)])
                ->latest()
                ->get()
            : collect();
        $reviewedOrderIds = auth()->check()
            ? auth()->user()->reviews()->where('product_id', $product->id)->pluck('order_id')->all()
            : [];

        return view('shop.product', [
            'product' => $product,
            'reviewOrders' => $reviewOrders,
            'reviewedOrderIds' => $reviewedOrderIds,
            'relatedProducts' => Product::with(['category', 'images', 'variants'])
                ->where('status', 'active')->where('category_id', $product->category_id)
                ->whereKeyNot($product->id)->take(4)->get(),
        ]);
    }
}
