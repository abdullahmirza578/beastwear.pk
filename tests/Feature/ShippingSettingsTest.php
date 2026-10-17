<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ShippingSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShippingSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_shipping_settings_are_saved_and_rendered_on_customer_pages(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);

        $this->actingAs($admin)->putJson(route('admin.shipping-settings.update'), [
            'nationwide' => true,
            'defaultCharge' => 425,
            'freeThreshold' => 6000,
            'karachiCharge' => 175,
            'otherCityCharge' => 325,
            'remoteAreaCharge' => 500,
            'karachiDays' => '2-3',
            'otherDays' => '4-6',
            'courier' => 'TCS',
            'codEnabled' => true,
            'codFee' => 75,
        ])->assertOk()
            ->assertJsonPath('freeThreshold', 6000)
            ->assertJsonPath('otherCityCharge', 325);

        $this->assertDatabaseHas('shipping_settings', [
            'id' => 1,
            'free_threshold' => 6000,
            'other_city_charge' => 325,
            'cod_fee' => 75,
        ]);

        $this->actingAs($admin)->getJson(route('admin.shipping-settings.data'))
            ->assertOk()
            ->assertJsonPath('freeThreshold', 6000)
            ->assertJsonPath('courier', 'TCS');

        $this->get(route('shipping'))
            ->assertOk()
            ->assertSee('6,000')
            ->assertSee('325')
            ->assertSee('2-3 working days');

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Free shipping over Rs 6,000');
    }

    public function test_checkout_uses_saved_city_rate_free_threshold_and_cod_fee(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $category = Category::create(['name' => 'T-Shirts', 'slug' => 't-shirts', 'status' => 'active']);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Shipping Test Tee',
            'slug' => 'shipping-test-tee',
            'sku' => 'BW-SHIP-01',
            'description' => 'Shipping test product',
            'price' => 2000,
            'stock_quantity' => 5,
            'status' => 'active',
        ]);
        ShippingSetting::current()->update([
            'free_threshold' => 6000,
            'karachi_charge' => 175,
            'other_city_charge' => 325,
            'cod_fee' => 75,
        ]);

        $this->actingAs($customer)->post(route('cart.items.store'), [
            'product_id' => $product->id,
            'quantity' => 1,
        ])->assertSessionHasNoErrors();
        $this->get(route('cart'))
            ->assertOk()
            ->assertSee('Rs 250')
            ->assertSee('Rs 75');

        $this->post(route('checkout.store'), [
            'customer_name' => 'Shipping Customer',
            'customer_phone' => '03001234567',
            'customer_email' => $customer->email,
            'shipping_address' => 'House 1, Main Road',
            'city' => 'Karachi',
            'payment_method' => 'COD',
        ])->assertRedirect();

        $order = Order::firstOrFail();
        $this->assertSame(250.0, (float) $order->shipping_fee);
        $this->assertSame(2250.0, (float) $order->total_amount);
    }

    public function test_checkout_rejects_cod_when_admin_disables_it(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $category = Category::create(['name' => 'T-Shirts', 'slug' => 't-shirts', 'status' => 'active']);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'COD Test Tee',
            'slug' => 'cod-test-tee',
            'sku' => 'BW-COD-01',
            'description' => 'COD test product',
            'price' => 2000,
            'stock_quantity' => 5,
            'status' => 'active',
        ]);
        ShippingSetting::current()->update(['cod_enabled' => false]);

        $this->actingAs($customer)->post(route('cart.items.store'), [
            'product_id' => $product->id,
            'quantity' => 1,
        ])->assertSessionHasNoErrors();

        $this->from(route('checkout'))->post(route('checkout.store'), [
            'customer_name' => 'Shipping Customer',
            'customer_phone' => '03001234567',
            'customer_email' => $customer->email,
            'shipping_address' => 'House 1, Main Road',
            'city' => 'Karachi',
            'payment_method' => 'COD',
        ])->assertRedirect(route('checkout'))
            ->assertSessionHasErrors('payment_method');

        $this->assertDatabaseCount('orders', 0);
    }
}
