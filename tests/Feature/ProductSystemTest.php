<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductSystemTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_manage_product_images_primary_image_and_visibility(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $category = Category::create(['name' => 'T-Shirts', 'slug' => 't-shirts', 'status' => 'active']);

        $this->actingAs($admin)->post(route('admin.products.store'), $this->productData($category) + [
            'main_image' => UploadedFile::fake()->create('front.jpg', 50, 'image/jpeg'),
            'gallery_images' => [UploadedFile::fake()->create('back.jpg', 50, 'image/jpeg')],
        ])->assertRedirect(route('admin.products.index'));

        $product = Product::where('sku', 'BW-IMAGE-001')->firstOrFail();
        $images = $product->images()->get();
        $this->assertCount(2, $images);
        $primaryImage = $images->firstWhere('is_primary', true);
        $galleryImage = $images->firstWhere('is_primary', false);
        $this->assertNotNull($primaryImage);
        $this->assertNotNull($galleryImage);
        $this->assertStringStartsWith('products/', $primaryImage->image);
        $this->assertStringStartsWith('products/', $galleryImage->image);
        $this->assertStringNotContainsString('storage/', $primaryImage->image);
        Storage::disk('public')->assertExists($primaryImage->image);
        Storage::disk('public')->assertExists($galleryImage->image);

        $this->actingAs($admin)->get(route('admin.products.index'))
            ->assertOk()
            ->assertSee($primaryImage->url, false);
        $this->get(route('admin.products.edit', ['id' => $product->id]))
            ->assertOk()
            ->assertSee($primaryImage->url, false)
            ->assertSee($galleryImage->url, false);
        $this->get(route('shop'))
            ->assertOk()
            ->assertSee(basename($primaryImage->image));
        $this->get(route('products.show', $product->slug))
            ->assertOk()
            ->assertSee($primaryImage->url, false)
            ->assertSee($galleryImage->url, false);

        $this->put(route('admin.products.update', $product), $this->productData($category, [
            'name' => 'Updated Cotton Tee',
            'sku' => 'BW-IMAGE-001',
            'status' => 'inactive',
            'primary_image_id' => $galleryImage->id,
        ]))->assertRedirect(route('admin.products.index'));

        $product->refresh();
        $this->assertSame('inactive', $product->status);
        $this->assertSame('Updated Cotton Tee', $product->name);
        $this->assertSame($galleryImage->id, $product->images()->where('is_primary', true)->value('id'));

        $this->delete(route('admin.products.destroy', $product))
            ->assertRedirect(route('admin.products.index'));
        Storage::disk('public')->assertMissing($primaryImage->image);
        Storage::disk('public')->assertMissing($galleryImage->image);
    }

    public function test_shop_and_category_only_expose_active_database_products(): void
    {
        $category = Category::create(['name' => 'Hoodies', 'slug' => 'hoodies', 'status' => 'active']);
        $product = Product::create($this->productData($category, [
            'name' => 'Database Hoodie',
            'slug' => 'database-hoodie',
            'sku' => 'BW-HOODIE-001',
        ]));
        $hidden = Product::create($this->productData($category, [
            'name' => 'Inactive Hoodie',
            'slug' => 'inactive-hoodie',
            'sku' => 'BW-HOODIE-002',
            'status' => 'inactive',
        ]));

        $this->get(route('shop'))
            ->assertOk()
            ->assertViewHas('shopProductIds', [$product->id])
            ->assertDontSee($hidden->name);

        $this->get(route('category', ['cat' => $category->slug]))
            ->assertOk()
            ->assertViewHas('category', $category)
            ->assertViewHas('categoryProductIds', [$product->id]);
    }

    public function test_search_uses_active_product_name_sku_and_category_queries(): void
    {
        $category = Category::create(['name' => 'Sweatshirts', 'slug' => 'sweatshirts', 'status' => 'active']);
        $product = Product::create($this->productData($category, [
            'name' => 'Everyday Layer',
            'slug' => 'everyday-layer',
            'sku' => 'BW-SWEAT-009',
        ]));

        $this->get(route('search', ['q' => 'BW-SWEAT-009']))
            ->assertOk()
            ->assertViewHas('searchProductIds', [$product->id])
            ->assertViewHas('searchResultCount', 1);

        $this->get(route('search', ['q' => 'Sweatshirts']))
            ->assertOk()
            ->assertViewHas('searchProductIds', [$product->id]);

        $this->get(route('search', ['q' => 'Everyday']))
            ->assertOk()
            ->assertViewHas('searchProductIds', [$product->id]);
    }

    public function test_product_details_load_by_active_slug_with_images_and_variants(): void
    {
        Storage::fake('public');
        $category = Category::create(['name' => 'T-Shirts', 'slug' => 't-shirts', 'status' => 'active']);
        $product = Product::create($this->productData($category, [
            'name' => 'Slug Loaded Tee',
            'slug' => 'slug-loaded-tee',
            'sku' => 'BW-SLUG-001',
            'description' => 'Description from the database.',
            'sale_price' => 1900,
        ]));
        Storage::disk('public')->put('products/product-detail.jpg', 'test image');
        $image = ProductImage::create([
            'product_id' => $product->id,
            'image' => 'products/product-detail.jpg',
            'sort_order' => 0,
            'is_primary' => true,
        ]);
        ProductVariant::create([
            'product_id' => $product->id,
            'size' => 'M',
            'color' => 'Black',
            'stock_quantity' => 3,
            'additional_price' => 150,
        ]);

        $this->get(route('products.show', $product->slug))
            ->assertOk()
            ->assertViewHas('product', fn (Product $loaded): bool => $loaded->is($product)
                && $loaded->category->is($category)
                && $loaded->variants->first()->additional_price === '150.00')
            ->assertSee($product->name)
            ->assertSee('Description from the database.')
            ->assertSee($image->url, false);

        $inactive = Product::create($this->productData($category, [
            'name' => 'Inactive Detail',
            'slug' => 'inactive-detail',
            'sku' => 'BW-INACTIVE-001',
            'status' => 'inactive',
        ]));
        $this->get(route('products.show', $inactive->slug))->assertNotFound();
    }

    private function productData(Category $category, array $overrides = []): array
    {
        return array_replace([
            'category_id' => $category->id,
            'name' => 'Cotton Tee',
            'slug' => 'cotton-tee',
            'sku' => 'BW-IMAGE-001',
            'description' => 'Cotton product description.',
            'short_description' => 'Soft cotton.',
            'price' => 2200,
            'sale_price' => null,
            'cost_price' => 1000,
            'stock_quantity' => 12,
            'status' => 'active',
            'featured' => false,
        ], $overrides);
    }
}
