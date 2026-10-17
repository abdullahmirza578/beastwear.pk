<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\SiteSetting;
use App\Models\ShippingSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer(['layouts.layout', 'home.index', 'pages.shipping', 'pages.contact'], function ($view): void {
            $view->with('shippingSettings', ShippingSetting::current());
            $view->with('siteSettings', SiteSetting::current());

            $catalog = Cache::remember('storefront.catalog.v3', now()->addMinutes(5), function (): array {
                $categories = Category::where('status', 'active')->orderBy('name')->get()
                    ->map(fn (Category $category) => [
                        'name' => $category->name,
                        'slug' => $category->slug,
                        'desc' => $category->description,
                        'image' => $category->image,
                    ])->values()->all();

                $products = Product::with(['category', 'images', 'variants'])
                    ->withCount(['reviews' => fn ($query) => $query->where('status', 'approved')])
                    ->withAvg(['reviews' => fn ($query) => $query->where('status', 'approved')], 'rating')
                    ->where('status', 'active')->latest()->get()
                    ->map(function (Product $product): array {
                        $images = $product->images->pluck('image')->values()->all();
                        $availableVariants = $product->variants->where('stock_quantity', '>', 0);
                        $sizes = $product->size_selection_enabled
                            ? $availableVariants->pluck('size')->filter()->unique()->values()->all()
                            : [];
                        $colors = $product->color_selection_enabled
                            ? $availableVariants->pluck('color')->filter()->unique()->values()->all()
                            : [];
                        $hasVariantSelections = $product->size_selection_enabled || $product->color_selection_enabled;
                        $stock = $hasVariantSelections && $product->variants->isNotEmpty()
                            ? $product->variants->sum('stock_quantity')
                            : $product->stock_quantity;
                        $image = $images[0] ?? null;

                        return [
                            'id' => $product->id,
                            'slug' => $product->slug,
                            'url' => route('products.show', $product->slug),
                            'name' => $product->name,
                            'sku' => $product->sku,
                            'category' => $product->category?->name ?? 'Uncategorised',
                            'categorySlug' => $product->category?->slug,
                            'price' => (float) $product->price,
                            'salePrice' => $product->sale_price === null ? null : (float) $product->sale_price,
                            'image' => $image,
                            'images' => $images,
                            'sizes' => $sizes,
                            'colors' => $colors,
                            'optionConfig' => [
                                'design' => $product->design_selection_enabled,
                                'size' => $product->size_selection_enabled,
                                'color' => $product->color_selection_enabled,
                            ],
                            'designs' => $product->design_selection_enabled
                                ? $product->selectableDesigns->map(fn (ProductImage $design) => [
                                    'id' => $design->id,
                                    'name' => $design->design_name ?: 'Design',
                                    'image' => ProductImage::urlFor($design->image),
                                ])->values()->all()
                                : [],
                            'rating' => (float) ($product->reviews_avg_rating ?? 0),
                            'reviews' => $product->reviews_count,
                            'stock' => $stock,
                            'isNew' => $product->created_at?->greaterThan(now()->subDays(30)) ?? false,
                            'bestSeller' => $product->featured,
                            'popularity' => $product->featured ? 90 : 50,
                            'added' => $product->created_at?->timestamp ?? 0,
                            'desc' => $product->description ?? '',
                            'fabric' => '',
                            'fit' => '',
                            'variants' => $availableVariants->map(fn ($variant) => [
                                'id' => $variant->id,
                                'size' => $variant->size,
                                'color' => $variant->color,
                                'stock' => $variant->stock_quantity,
                                'additionalPrice' => (float) $variant->additional_price,
                            ])->values()->all(),
                        ];
                    })->values()->all();

                return compact('categories', 'products');
            });

            $catalog['categories'] = array_map(function (array $category): array {
                $category['image'] = ProductImage::urlFor($category['image']);

                return $category;
            }, $catalog['categories']);
            $catalog['products'] = array_map(function (array $product): array {
                $product['images'] = array_map(
                    fn (string $image): ?string => ProductImage::urlFor($image),
                    $product['images']
                );
                $product['images'] = array_values(array_filter($product['images']));
                $product['image'] = $product['images'][0] ?? null;

                return $product;
            }, $catalog['products']);

            $productSlug = request()->route('slug');
            $reviews = $productSlug
                ? \App\Models\Review::with('user')
                    ->where('status', 'approved')
                    ->whereHas('product', fn ($query) => $query->where('slug', $productSlug))
                    ->latest()->get()
                    ->map(fn ($review) => [
                        'name' => $review->user->name,
                        'city' => 'Verified customer',
                        'rating' => $review->rating,
                        'date' => $review->created_at->format('d M Y'),
                        'text' => $review->comment ?? '',
                    ])->values()->all()
                : [];

            $view->with('storefrontCatalog', $catalog)
                ->with('storefrontReviews', $reviews)
                ->with('storefrontProductSlug', $productSlug)
                ->with('serverCartCount', auth()->check()
                    ? (auth()->user()->cart()->first()?->items()->sum('quantity') ?? 0)
                    : 0)
                ->with('serverWishlistIds', auth()->check()
                    ? (auth()->user()->wishlist()->first()?->items()->pluck('product_id')->all() ?? [])
                    : []);
        });
    }
}
