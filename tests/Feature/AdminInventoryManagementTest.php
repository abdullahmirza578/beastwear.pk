<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminInventoryManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_inventory_api_lists_database_products_and_available_stock(): void
    {
        $admin = $this->admin();
        $category = Category::create([
            'name' => 'T-Shirts',
            'slug' => 't-shirts',
            'status' => 'active',
        ]);
        $product = $this->product($category, ['stock_quantity' => 12]);
        $order = Order::create([
            'order_number' => 'BW-261012-INV00001',
            'customer_name' => 'Inventory Customer',
            'customer_phone' => '03001234567',
            'shipping_address' => 'House 1, Main Road',
            'city' => 'Lahore',
            'subtotal' => 2000,
            'discount' => 0,
            'shipping_fee' => 250,
            'total_amount' => 2250,
            'payment_method' => 'COD',
            'order_status' => 'delivered',
        ]);
        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'sku' => $product->sku,
            'quantity' => 2,
            'price' => 2000,
            'total' => 4000,
        ]);

        $this->actingAs($admin)
            ->getJson(route('admin.inventory.data'))
            ->assertOk()
            ->assertJsonPath('products.0.id', $product->id)
            ->assertJsonPath('products.0.stock', 12)
            ->assertJsonPath('products.0.sold', 2)
            ->assertJsonPath('products.0.category.slug', 't-shirts')
            ->assertJsonPath('products.0.lowStock', 10)
            ->assertJsonPath('categories.0.name', 'T-Shirts');
    }

    public function test_admin_can_adjust_stock_and_set_a_persistent_low_stock_threshold(): void
    {
        $admin = $this->admin();
        $product = $this->product(null, ['stock_quantity' => 8]);

        $this->actingAs($admin)
            ->patchJson(route('admin.inventory.stock', $product), [
                'mode' => 'add',
                'quantity' => 5,
                'reason' => 'New stock received',
            ])
            ->assertOk()
            ->assertJsonPath('stock', 13);

        $this->actingAs($admin)
            ->patchJson(route('admin.inventory.threshold', $product), ['lowStock' => 3])
            ->assertOk()
            ->assertJsonPath('lowStock', 3);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock_quantity' => 13,
            'low_stock_threshold' => 3,
        ]);
        $this->assertDatabaseHas('product_stock_adjustments', [
            'product_id' => $product->id,
            'admin_id' => $admin->id,
            'stock_before' => 8,
            'stock_after' => 13,
            'reason' => 'New stock received',
        ]);
    }

    public function test_stock_adjustments_update_variant_quantities_and_reject_excess_stock(): void
    {
        $admin = $this->admin();
        $product = $this->product(null, ['stock_quantity' => 10]);
        $first = ProductVariant::create([
            'product_id' => $product->id,
            'size' => 'S',
            'stock_quantity' => 3,
            'additional_price' => 0,
        ]);
        $second = ProductVariant::create([
            'product_id' => $product->id,
            'size' => 'M',
            'stock_quantity' => 7,
            'additional_price' => 0,
        ]);

        $this->actingAs($admin)
            ->patchJson(route('admin.inventory.stock', $product), [
                'mode' => 'set',
                'quantity' => 15,
                'reason' => 'Stock count correction',
            ])
            ->assertOk()
            ->assertJsonPath('stock', 15);

        $this->assertSame(15, (int) $first->fresh()->stock_quantity + (int) $second->fresh()->stock_quantity);

        $this->actingAs($admin)
            ->patchJson(route('admin.inventory.stock', $product), [
                'mode' => 'add',
                'quantity' => 1000000,
                'reason' => 'New stock received',
            ])
            ->assertUnprocessable();
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin', 'status' => 'active']);
    }

    private function product(?Category $category, array $overrides = []): Product
    {
        return Product::create(array_merge([
            'category_id' => $category?->id,
            'name' => 'Inventory Test Product',
            'slug' => 'inventory-test-product',
            'sku' => 'INV-TEST-001',
            'price' => 2000,
            'stock_quantity' => 0,
            'status' => 'active',
        ], $overrides));
    }
}
