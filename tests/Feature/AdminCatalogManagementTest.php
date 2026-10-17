<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCatalogManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_a_product_and_its_size_color_variants(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $category = Category::create(['name' => 'T-Shirts', 'slug' => 't-shirts', 'status' => 'active']);

        $response = $this->actingAs($admin)->post(route('admin.products.store'), [
            'name' => 'Classic Cotton Tee',
            'sku' => 'bw-tee-002',
            'category_id' => $category->id,
            'short_description' => 'A cotton tee',
            'description' => 'Made in Pakistan',
            'price' => 2200,
            'sale_price' => 1999,
            'cost_price' => 1000,
            'stock_quantity' => 24,
            'status' => 'active',
            'featured' => '1',
            'size_selection_enabled' => '1',
            'color_selection_enabled' => '1',
            'design_selection_enabled' => '0',
            'variant_sizes' => 'S, M, L',
            'variant_colors' => 'Black, White',
            'variant_stock' => 4,
            'variant_additional_price' => 100,
        ]);

        $product = Product::where('sku', 'BW-TEE-002')->firstOrFail();
        $response->assertRedirect(route('admin.products.index'));
        $this->assertSame(6, $product->variants()->count());
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'slug' => 'classic-cotton-tee',
            'category_id' => $category->id,
            'featured' => true,
        ]);
    }

    public function test_admin_can_create_categories_and_regular_users_cannot_manage_them(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $response = $this->actingAs($admin)->post(route('admin.categories.store'), [
            'name' => 'Oversized T-Shirts',
            'description' => 'Relaxed fit styles',
            'status' => 'active',
        ]);

        $response->assertRedirect(route('admin.categories.index'));
        $this->assertDatabaseHas('categories', ['slug' => 'oversized-t-shirts']);

        $customer = User::factory()->create(['role' => 'customer']);
        $this->actingAs($customer)
            ->post(route('admin.categories.store'), [
                'name' => 'Restricted',
                'status' => 'active',
            ])
            ->assertForbidden();
    }
}
