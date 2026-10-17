<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ShippingSetting;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PrepaidPaymentFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_place_prepaid_order_upload_receipt_and_admin_can_approve_it(): void
    {
        Storage::fake('local');
        [$customer, $product] = $this->createCustomerWithCart();
        SiteSetting::current()->update([
            'jazzcash_account_title' => 'BeastWear',
            'jazzcash_account_number' => '03001234567',
        ]);

        $this->actingAs($customer)->get(route('checkout'))
            ->assertOk()
            ->assertSee('JazzCash')
            ->assertSee('03001234567');
        $this->actingAs($customer)->post(route('checkout.store'), $this->checkoutData('JazzCash'))
            ->assertSessionHasNoErrors();

        $order = Order::firstOrFail();
        $payment = $order->payments()->firstOrFail();
        $this->assertSame('JazzCash', $order->payment_method);
        $this->assertSame('pending', $order->payment_status);
        $this->assertSame(4, $product->fresh()->stock_quantity);

        $this->actingAs($customer)->post(route('orders.payment-proof', $order), [
            'transaction_id' => 'JC-12345',
            'proof' => UploadedFile::fake()->create('receipt.pdf', 10, 'application/pdf'),
        ])->assertSessionHasNoErrors()
            ->assertRedirect(route('orders.show', $order));

        $payment->refresh();
        $this->assertSame('JC-12345', $payment->transaction_id);
        Storage::disk('local')->assertExists($payment->proof_path);
        $this->actingAs($customer)->get(route('orders.show', $order))
            ->assertOk()
            ->assertSee('awaiting admin verification');

        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $this->actingAs($admin)->get(route('admin.payments.index'))
            ->assertOk()
            ->assertSee($order->order_number)
            ->assertSee('View receipt');
        $this->actingAs($admin)->get(route('admin.payments.proof', $payment))->assertOk();
        $this->actingAs($admin)->patch(route('admin.payments.approve', $payment))
            ->assertSessionHas('status');

        $this->assertSame('paid', $order->fresh()->payment_status);
        $this->assertSame('confirmed', $order->fresh()->order_status);
        $this->assertSame('paid', $payment->fresh()->status);
        $this->assertNotNull($payment->fresh()->paid_at);
    }

    public function test_rejected_prepaid_payment_cancels_order_and_restores_reserved_product_stock(): void
    {
        Storage::fake('local');
        [$customer] = $this->createCustomerWithCart();
        SiteSetting::current()->update([
            'easypaisa_account_title' => 'BeastWear',
            'easypaisa_account_number' => '03007654321',
        ]);
        $this->actingAs($customer)->post(route('checkout.store'), $this->checkoutData('EasyPaisa'))
            ->assertSessionHasNoErrors();

        $order = Order::firstOrFail();
        $payment = $order->payments()->firstOrFail();
        $this->actingAs($customer)->post(route('orders.payment-proof', $order), [
            'proof' => UploadedFile::fake()->create('receipt.pdf', 10, 'application/pdf'),
        ])->assertSessionHasNoErrors();
        $payment->refresh();
        $proofPath = $payment->proof_path;

        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $this->actingAs($admin)->patch(route('admin.payments.reject', $payment))
            ->assertSessionHas('status');

        $this->assertSame('cancelled', $order->fresh()->order_status);
        $this->assertSame('rejected', $order->fresh()->payment_status);
        $this->assertSame('rejected', $payment->fresh()->status);
        Storage::disk('local')->assertMissing($proofPath);
        $this->assertSame(5, Product::whereKey($order->items()->value('product_id'))->value('stock_quantity'));
        $this->actingAs($customer)->get(route('orders.show', $order))
            ->assertOk()
            ->assertSee('This order was cancelled');
    }

    public function test_customer_cannot_upload_proof_for_another_users_order_or_upload_invalid_files(): void
    {
        Storage::fake('local');
        [$owner] = $this->createCustomerWithCart();
        SiteSetting::current()->update([
            'bank_name' => 'Test Bank',
            'bank_account_title' => 'BeastWear',
            'bank_account_number' => 'PK00TEST',
        ]);
        $this->actingAs($owner)->post(route('checkout.store'), $this->checkoutData('Bank Transfer'))
            ->assertSessionHasNoErrors();
        $order = Order::firstOrFail();

        $otherCustomer = User::factory()->create(['role' => 'customer']);
        $this->actingAs($otherCustomer)->post(route('orders.payment-proof', $order), [
            'proof' => UploadedFile::fake()->create('receipt.pdf', 10, 'application/pdf'),
        ])->assertNotFound();

        $this->actingAs($owner)->from(route('orders.show', $order))
            ->post(route('orders.payment-proof', $order), [
                'proof' => UploadedFile::fake()->create('receipt.exe', 10, 'application/octet-stream'),
            ])->assertSessionHasErrors('proof');
    }

    public function test_prepaid_checkout_remains_available_when_cod_is_disabled(): void
    {
        [$customer] = $this->createCustomerWithCart();
        SiteSetting::current()->update([
            'cod_enabled' => false,
            'bank_name' => 'Test Bank',
            'bank_account_title' => 'BeastWear',
            'bank_account_number' => 'PK00TEST',
        ]);
        ShippingSetting::current()->update(['cod_enabled' => false]);

        $this->actingAs($customer)->get(route('checkout'))
            ->assertOk()
            ->assertSee('Bank Transfer')
            ->assertDontSee('Cash on Delivery (COD)');
        $this->actingAs($customer)->post(route('checkout.store'), $this->checkoutData('Bank Transfer'))
            ->assertSessionHasNoErrors();

        $order = Order::firstOrFail();
        $this->assertSame('Bank Transfer', $order->payment_method);
        $this->assertSame(0.0, (float) $order->shipping_fee - \App\Models\ShippingSetting::current()->deliveryFee($order->subtotal, $order->city));
    }

    private function createCustomerWithCart(): array
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $category = Category::create(['name' => 'T-Shirts', 'slug' => 't-shirts', 'status' => 'active']);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Prepaid Test Tee',
            'slug' => 'prepaid-test-tee',
            'sku' => 'BW-PREPAID-01',
            'description' => 'Prepaid payment test item',
            'price' => 2000,
            'stock_quantity' => 5,
            'status' => 'active',
        ]);
        $this->actingAs($customer)->post(route('cart.items.store'), [
            'product_id' => $product->id,
            'quantity' => 1,
        ])->assertSessionHasNoErrors();

        return [$customer, $product];
    }

    private function checkoutData(string $paymentMethod): array
    {
        return [
            'customer_name' => 'Beast Customer',
            'customer_phone' => '03001234567',
            'customer_email' => 'customer@example.test',
            'shipping_address' => 'House 1, Main Road',
            'city' => 'Karachi',
            'payment_method' => $paymentMethod,
        ];
    }
}
