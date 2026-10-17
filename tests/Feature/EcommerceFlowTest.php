<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ShippingMethod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EcommerceFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_registration_creates_customer_role_cart_and_wishlist(): void
    {
        $response = $this->post(route('register.store'), [
            'name' => 'Beast Customer',
            'email' => 'customer@example.test',
            'phone' => '03001234567',
            'password' => 'a-secure-password',
            'password_confirmation' => 'a-secure-password',
            'terms' => '1',
        ]);

        $user = User::where('email', 'customer@example.test')->firstOrFail();
        $response->assertRedirect(route('account'));
        $this->assertSame('customer', $user->role);
        $this->assertNotNull($user->cart);
        $this->assertNotNull($user->wishlist);
    }

    public function test_cod_checkout_snapshots_order_items_and_decrements_variant_stock(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $category = Category::create(['name' => 'T-Shirts', 'slug' => 't-shirts', 'status' => 'active']);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Classic Tee',
            'slug' => 'classic-tee',
            'sku' => 'BW-TEE-01',
            'description' => 'Cotton tee',
            'price' => 2000,
            'sale_price' => 1800,
            'stock_quantity' => 20,
            'status' => 'active',
            'size_selection_enabled' => true,
            'color_selection_enabled' => true,
        ]);
        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'size' => 'M',
            'color' => 'Black',
            'stock_quantity' => 5,
            'additional_price' => 100,
        ]);
        ShippingMethod::create([
            'name' => 'Standard',
            'price' => 250,
            'estimated_days' => 3,
            'status' => 'active',
        ]);

        $this->actingAs($customer)
            ->post(route('cart.items.store'), [
                'product_id' => $product->id,
                'size' => 'M',
                'color' => 'Black',
                'quantity' => 2,
            ])->assertSessionHasNoErrors();

        $response = $this->post(route('checkout.store'), [
            'customer_name' => 'Beast Customer',
            'customer_phone' => '03001234567',
            'customer_email' => $customer->email,
            'shipping_address' => 'House 1, Main Road',
            'city' => 'Lahore',
            'postal_code' => '54000',
            'payment_method' => 'COD',
        ]);
        $response->assertSessionHasNoErrors();

        $order = \App\Models\Order::firstOrFail();
        $this->assertMatchesRegularExpression('/^BW-TRK-[A-Z0-9]{12}$/', $order->public_tracking_id);
        $response->assertRedirect(route('orders.show', $order));
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_name' => 'Classic Tee',
            'sku' => 'BW-TEE-01',
            'size' => 'M',
            'color' => 'Black',
            'quantity' => 2,
            'price' => 1900,
            'total' => 3800,
        ]);
        $this->assertSame(3, $variant->fresh()->stock_quantity);
        $this->assertSame('COD', $order->payment_method);
        $this->assertSame('pending', $order->payments()->firstOrFail()->status);
        $this->assertDatabaseCount('cart_items', 0);

        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $admin->is_admin = true;
        $admin->save();

        $this->actingAs($admin)
            ->getJson(route('admin.data.orders.index'))
            ->assertOk()
            ->assertJsonPath('0.id', $order->order_number)
            ->assertJsonPath('0.publicTrackingId', $order->public_tracking_id)
            ->assertJsonPath('0.items.0.name', 'Classic Tee');

        $this->post(route('track-order.lookup'), [
            'order_number' => $order->public_tracking_id,
            'customer_phone' => '+92 300 123 4567',
        ])
            ->assertOk()
            ->assertSee($order->public_tracking_id);
    }

    public function test_guests_are_redirected_before_opening_their_cart(): void
    {
        $this->get(route('cart'))
            ->assertRedirect(route('login'));
    }
}
