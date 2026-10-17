<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ShippingSetting;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SiteSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_settings_persist_and_update_customer_store_and_social_details(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);

        $this->actingAs($admin)->post(route('admin.settings.update'), [
            'storeName' => 'BeastWear Test Store',
            'tagline' => 'Built for everyday.',
            'email' => 'hello@example.test',
            'phone' => '021-12345678',
            'whatsapp' => '03001234567',
            'address' => 'Test address, Karachi',
            'instagram' => 'https://instagram.com/beastwear-test',
            'facebook' => 'https://facebook.com/beastwear-test',
            'tiktok' => 'https://tiktok.com/@beastwear-test',
            'youtube' => 'https://youtube.com/@beastwear-test',
            'minOrder' => 1500,
            'orderConfirmation' => 'whatsapp',
            'codEnabled' => '1',
            'autoConfirmPrepaid' => '1',
            'lowStockAlerts' => '0',
            'maintenance' => '0',
            'currency' => 'PKR',
            'timezone' => 'Asia/Karachi',
            'bankName' => 'Test Bank',
            'bankAccountTitle' => 'BeastWear Test Store',
            'bankAccountNumber' => 'PK00TEST',
            'jazzcashAccountTitle' => 'BeastWear Test Store',
            'jazzcashAccountNumber' => '03001234567',
            'logo' => UploadedFile::fake()->create('store-logo.png', 100, 'image/png'),
            'favicon' => UploadedFile::fake()->create('favicon.png', 50, 'image/png'),
        ])->assertOk()
            ->assertJsonPath('storeName', 'BeastWear Test Store')
            ->assertJsonPath('minOrder', 1500)
            ->assertJsonPath('currency', 'PKR')
            ->assertJsonPath('bankName', 'Test Bank')
            ->assertJsonPath('jazzcashAccountNumber', '03001234567');

        $settings = SiteSetting::firstOrFail();
        $this->assertStringStartsWith('site-settings/', $settings->logo);
        $this->assertStringStartsWith('site-settings/', $settings->favicon);
        Storage::disk('public')->assertExists($settings->logo);
        Storage::disk('public')->assertExists($settings->favicon);
        $this->assertTrue(ShippingSetting::current()->cod_enabled);
        $this->actingAs($admin)->getJson(route('admin.settings.data'))
            ->assertOk()
            ->assertJsonPath('storeName', 'BeastWear Test Store')
            ->assertJsonPath('instagram', 'https://instagram.com/beastwear-test')
            ->assertJsonPath('logo', '/storage/'.$settings->logo);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('BeastWear Test Store')
            ->assertSee('Built for everyday.');
        $this->get(route('contact'))
            ->assertOk()
            ->assertSee('hello@example.test')
            ->assertSee('Test address, Karachi')
            ->assertSee('https://instagram.com/beastwear-test');
    }

    public function test_customer_whatsapp_buttons_open_the_store_whatsapp_number(): void
    {
        $response = $this->get(route('home'))->assertOk();

        $this->assertSame(
            2,
            substr_count($response->getContent(), 'https://wa.me/923458848846'),
            'Both the drawer and floating WhatsApp buttons should open the store WhatsApp chat.'
        );
    }

    public function test_minimum_order_setting_is_shown_and_enforced_at_checkout(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $category = Category::create(['name' => 'T-Shirts', 'slug' => 't-shirts', 'status' => 'active']);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Minimum Order Tee',
            'slug' => 'minimum-order-tee',
            'sku' => 'BW-MIN-01',
            'description' => 'Minimum-order test item',
            'price' => 800,
            'stock_quantity' => 5,
            'status' => 'active',
        ]);
        SiteSetting::current()->update(['min_order' => 1000]);

        $this->actingAs($customer)->post(route('cart.items.store'), [
            'product_id' => $product->id,
            'quantity' => 1,
        ])->assertSessionHasNoErrors();

        $this->get(route('checkout'))->assertOk()->assertSee('Minimum order: Rs 1,000');
        $this->from(route('checkout'))->post(route('checkout.store'), [
            'customer_name' => 'Minimum Order Customer',
            'customer_phone' => '03001234567',
            'customer_email' => $customer->email,
            'shipping_address' => 'House 1, Main Road',
            'city' => 'Karachi',
            'payment_method' => 'COD',
        ])->assertRedirect(route('checkout'))
            ->assertSessionHasErrors('minimum_order');

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_maintenance_mode_blocks_storefront_but_keeps_admin_access(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        SiteSetting::current()->update(['maintenance' => true]);

        $this->get(route('home'))->assertStatus(503)->assertSee('We are making a few updates');
        $this->actingAs($admin)->get(route('admin.settings'))
            ->assertOk()
            ->assertSee('General Settings');
    }

    public function test_currency_settings_only_accept_pkr_until_conversion_is_configured(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);

        $this->actingAs($admin)->postJson(route('admin.settings.update'), [
            'storeName' => 'BeastWear.pk',
            'tagline' => '',
            'email' => 'support@beastwear.pk',
            'phone' => '021-34567890',
            'whatsapp' => '0300-1234567',
            'address' => '',
            'instagram' => '',
            'facebook' => '',
            'tiktok' => '',
            'youtube' => '',
            'minOrder' => 1000,
            'orderConfirmation' => 'whatsapp',
            'codEnabled' => '1',
            'autoConfirmPrepaid' => '1',
            'lowStockAlerts' => '1',
            'maintenance' => '0',
            'currency' => 'USD',
            'timezone' => 'Asia/Karachi',
        ])->assertUnprocessable()->assertJsonValidationErrors('currency');
    }

    public function test_shipping_settings_cod_toggle_stays_in_sync_with_order_settings(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);

        $this->actingAs($admin)->putJson(route('admin.shipping-settings.update'), [
            'nationwide' => true,
            'defaultCharge' => 250,
            'freeThreshold' => 5000,
            'karachiCharge' => 150,
            'otherCityCharge' => 250,
            'remoteAreaCharge' => 350,
            'karachiDays' => '1-2',
            'otherDays' => '3-5',
            'courier' => 'Call Courier',
            'codEnabled' => false,
            'codFee' => 100,
        ])->assertOk();

        $this->assertFalse(SiteSetting::current()->cod_enabled);
    }
}
