<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(): View
    {
        return view('admin.products.index', [
            'products' => Product::with(['category', 'images', 'variants'])->latest()->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('admin.products.create', [
            'categories' => Category::orderBy('name')->get(),
            'product' => new Product(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $product = DB::transaction(function () use ($request, $data) {
            $product = Product::create($this->productAttributes($data));
            $newDesignIds = $this->saveImages($request, $product);
            $this->syncVariants($product, $data);
            $this->syncSelectableDesigns($product, $data, $newDesignIds);

            return $product;
        });

        $this->clearCatalogCache();

        return redirect()->route('admin.products.index')->with('status', "{$product->name} created.");
    }

    public function edit(Request $request): View
    {
        $product = Product::with(['images', 'variants'])->find($request->query('id'));
        $product ??= new Product();

        return view('admin.products.edit', [
            'product' => $product,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $data = $this->validated($request, $product);
        DB::transaction(function () use ($request, $data, $product) {
            $product->update($this->productAttributes($data, $product));
            $newDesignIds = $this->saveImages($request, $product);
            $this->syncVariants($product, $data);
            $this->syncSelectableDesigns($product, $data, $newDesignIds);
        });

        $this->clearCatalogCache();

        return redirect()->route('admin.products.index')->with('status', "{$product->name} updated.");
    }

    public function destroy(Product $product): RedirectResponse
    {
        $imagePaths = $product->images()->pluck('image');
        $product->delete();
        $this->deleteStoredImages($imagePaths);
        $this->clearCatalogCache();

        return redirect()->route('admin.products.index')->with('status', 'Product deleted.');
    }

    private function validated(Request $request, ?Product $product = null): array
    {
        $primaryImageRule = Rule::exists('product_images', 'id');
        if ($product) {
            $primaryImageRule->where(fn ($query) => $query->where('product_id', $product->id));
        }

        $validator = validator($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['required', 'string', 'max:100', Rule::unique('products', 'sku')->ignore($product?->id)],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string', 'max:10000'],
            'price' => ['required', 'numeric', 'gt:0', 'max:99999999'],
            'sale_price' => ['nullable', 'numeric', 'gte:0', 'lte:price'],
            'cost_price' => ['nullable', 'numeric', 'gte:0'],
            'stock_quantity' => ['required', 'integer', 'min:0', 'max:1000000'],
            'status' => ['required', Rule::in(['active', 'inactive', 'draft'])],
            'featured' => ['sometimes', 'boolean'],
            'design_selection_enabled' => ['sometimes', 'boolean'],
            'size_selection_enabled' => ['sometimes', 'boolean'],
            'color_selection_enabled' => ['sometimes', 'boolean'],
            'variant_sizes' => ['nullable', 'string', 'max:255'],
            'variant_colors' => ['nullable', 'string', 'max:255'],
            'variant_stock' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'variant_additional_price' => ['nullable', 'numeric', 'gte:0'],
            'main_image' => ['nullable', 'image', 'max:5120'],
            'gallery_images' => ['nullable', 'array', 'max:10'],
            'gallery_images.*' => ['image', 'max:5120'],
            'design_images' => ['nullable', 'array', 'max:10'],
            'design_images.*' => ['image', 'max:5120'],
            'primary_image_id' => [$product ? 'nullable' : 'prohibited', 'integer', $primaryImageRule],
            'design_image_ids' => ['nullable', 'array', 'max:10'],
            'design_image_ids.*' => ['integer', 'distinct', 'exists:product_images,id'],
            'design_names' => ['nullable', 'array'],
            'design_names.*' => ['nullable', 'string', 'max:100'],
        ]);
        $validator->after(function ($validator) use ($request, $product): void {
            $sizes = collect(explode(',', (string) $request->input('variant_sizes')))
                ->map(fn ($size) => trim($size))->filter()->unique();
            $colors = collect(explode(',', (string) $request->input('variant_colors')))
                ->map(fn ($color) => trim($color))->filter()->unique();

            if ($sizes->contains(fn ($size) => Str::length($size) > 20)) {
                $validator->errors()->add('variant_sizes', 'Each size must be 20 characters or fewer.');
            }
            if ($colors->contains(fn ($color) => Str::length($color) > 50)) {
                $validator->errors()->add('variant_colors', 'Each color must be 50 characters or fewer.');
            }
            if (max(1, $sizes->count()) * max(1, $colors->count()) > 100) {
                $validator->errors()->add('variant_colors', 'The size and color selections may create at most 100 variants.');
            }

            $selectedDesigns = collect($request->input('design_image_ids', []));
            if ($request->boolean('design_selection_enabled') && $selectedDesigns->isEmpty()
                && ! $request->hasFile('design_images')
                && ! ($product?->selectableDesigns()->exists() ?? false)) {
                $validator->errors()->add('design_image_ids', 'Select at least one product image to use as a design.');
            }
            if ($product && $selectedDesigns->isNotEmpty()) {
                $validDesignIds = $product->images()
                    ->whereIn('id', $selectedDesigns)
                    ->pluck('id')
                    ->map(fn ($id) => (string) $id);
                if ($validDesignIds->count() !== $selectedDesigns->unique()->count()) {
                    $validator->errors()->add('design_image_ids', 'Design images must belong to this product.');
                }
            }

            if (! $request->boolean('size_selection_enabled') && $sizes->isNotEmpty()) {
                $validator->errors()->add('variant_sizes', 'Clear sizes when Size Selection is OFF.');
            }
            if (! $request->boolean('color_selection_enabled') && $colors->isNotEmpty()) {
                $validator->errors()->add('variant_colors', 'Clear colors when Color Selection is OFF.');
            }
            if ($request->boolean('size_selection_enabled') && $sizes->isEmpty()) {
                $validator->errors()->add('variant_sizes', 'Add at least one size when Size Selection is ON.');
            }
            if ($request->boolean('color_selection_enabled') && $colors->isEmpty()) {
                $validator->errors()->add('variant_colors', 'Add at least one color when Color Selection is ON.');
            }
        });

        return $validator->validate();
    }

    private function productAttributes(array $data, ?Product $product = null): array
    {
        $slug = Str::slug($data['name']);
        if (Product::where('slug', $slug)->when($product, fn ($query) => $query->whereKeyNot($product->id))->exists()) {
            $slug .= '-'.Str::lower(Str::random(5));
        }

        return [
            'category_id' => $data['category_id'] ?? null,
            'name' => $data['name'],
            'slug' => $slug,
            'sku' => strtoupper($data['sku']),
            'description' => $data['description'] ?? null,
            'short_description' => $data['short_description'] ?? null,
            'price' => $data['price'],
            'sale_price' => $data['sale_price'] ?? null,
            'cost_price' => $data['cost_price'] ?? null,
            'stock_quantity' => $data['stock_quantity'],
            'status' => $data['status'],
            'featured' => (bool) ($data['featured'] ?? false),
            'design_selection_enabled' => (bool) ($data['design_selection_enabled'] ?? false),
            'size_selection_enabled' => (bool) ($data['size_selection_enabled'] ?? false),
            'color_selection_enabled' => (bool) ($data['color_selection_enabled'] ?? false),
        ];
    }

    private function syncSelectableDesigns(Product $product, array $data, array $newDesignIds = []): void
    {
        $product->images()->update([
            'is_selectable_design' => false,
        ]);

        if (! ($data['design_selection_enabled'] ?? false)) {
            return;
        }

        foreach (array_merge($data['design_image_ids'] ?? [], $newDesignIds) as $index => $imageId) {
            $image = $product->images()->whereKey($imageId)->first();
            $product->images()->whereKey($imageId)->update([
                'is_selectable_design' => true,
                'design_name' => trim($data['design_names'][$imageId] ?? '')
                    ?: $image?->design_name
                    ?: 'Design '.($index + 1),
            ]);
        }
    }

    private function saveImages(Request $request, Product $product): array
    {
        $newDesignIds = [];
        $newPrimary = null;
        if ($request->hasFile('main_image')) {
            $path = $request->file('main_image')->storePublicly('products', ['disk' => 'public']);
            $newPrimary = ProductImage::create([
                'product_id' => $product->id,
                'image' => $path,
                'sort_order' => 0,
                'is_primary' => false,
            ]);
        }

        foreach ($request->file('gallery_images', []) as $image) {
            $path = $image->storePublicly('products', ['disk' => 'public']);
            $product->images()->create([
                'image' => $path,
                'sort_order' => (int) $product->images()->max('sort_order') + 1,
                'is_primary' => false,
            ]);
        }

        foreach ($request->file('design_images', []) as $image) {
            $path = $image->storePublicly('products', ['disk' => 'public']);
            $name = Str::of(pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME))
                ->replace(['-', '_'], ' ')
                ->squish()
                ->limit(100, '');
            $design = $product->images()->create([
                'image' => $path,
                'sort_order' => (int) $product->images()->max('sort_order') + 1,
                'is_primary' => false,
                'is_selectable_design' => true,
                'design_name' => $name->value() ?: 'Design',
            ]);
            $newDesignIds[] = $design->id;
        }

        if ($newPrimary) {
            $product->images()->update(['is_primary' => false]);
            $newPrimary->update(['is_primary' => true]);
        } elseif ($request->filled('primary_image_id')) {
            $product->images()->update(['is_primary' => false]);
            $product->images()->whereKey($request->integer('primary_image_id'))->update(['is_primary' => true]);
        } elseif (! $product->images()->where('is_primary', true)->exists()) {
            $firstImage = $product->images()->orderBy('sort_order')->orderBy('id')->first();
            $firstImage?->update(['is_primary' => true]);
        }

        return $newDesignIds;
    }

    private function deleteStoredImages(iterable $images): void
    {
        $paths = collect($images)
            ->filter(fn ($image) => is_string($image))
            ->map(fn ($image) => Str::after(str_replace('\\', '/', $image), 'storage/'))
            ->filter(fn ($image) => str_starts_with($image, 'products/'))
            ->values()
            ->all();

        if ($paths !== []) {
            Storage::disk('public')->delete($paths);
        }
    }

    private function syncVariants(Product $product, array $data): void
    {
        $sizes = collect(explode(',', $data['variant_sizes'] ?? ''))->map(fn ($value) => trim($value))->filter()->unique();
        $colors = collect(explode(',', $data['variant_colors'] ?? ''))->map(fn ($value) => trim($value))->filter()->unique();
        if ($sizes->isEmpty() && $colors->isEmpty()) {
            return;
        }

        $sizes = $sizes->isEmpty() ? collect([null]) : $sizes;
        $colors = $colors->isEmpty() ? collect([null]) : $colors;
        $keep = [];
        foreach ($sizes as $size) {
            foreach ($colors as $color) {
                $variant = $product->variants()->updateOrCreate(
                    ['size' => $size, 'color' => $color],
                    [
                        'stock_quantity' => (int) ($data['variant_stock'] ?? 0),
                        'additional_price' => $data['variant_additional_price'] ?? 0,
                    ]
                );
                $keep[] = $variant->id;
            }
        }
        $product->variants()->whereNotIn('id', $keep)->delete();
    }

    private function clearCatalogCache(): void
    {
        \Illuminate\Support\Facades\Cache::forget('storefront.catalog.v3');
    }
}
