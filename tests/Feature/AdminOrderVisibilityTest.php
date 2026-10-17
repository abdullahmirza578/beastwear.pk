<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminOrderVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_orders_api_returns_orders_created_by_checkout(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);
        $admin->is_admin = true;
        $admin->save();

        $order = Order::create([
            'order_number' => 'BW-261011-TEST1234',
            'customer_name' => 'Test Customer',
            'customer_phone' => '03001234567',
            'customer_email' => 'customer@example.test',
            'shipping_address' => 'House 1, Main Road',
            'city' => 'Lahore',
            'subtotal' => 2000,
            'discount' => 0,
            'shipping_fee' => 250,
            'total_amount' => 2250,
            'payment_method' => 'COD',
            'order_status' => 'pending',
            'payment_status' => 'pending',
        ]);
        OrderItem::create([
            'order_id' => $order->id,
            'product_name' => 'Classic Tee',
            'sku' => 'BW-TEE-01',
            'quantity' => 1,
            'price' => 2000,
            'total' => 2000,
        ]);

        $this->actingAs($admin)
            ->getJson(route('admin.data.orders.index'))
            ->assertOk()
            ->assertJsonPath('0.id', $order->order_number)
            ->assertJsonPath('0.customer', 'Test Customer')
            ->assertJsonPath('0.status', 'Pending')
            ->assertJsonPath('0.payment', 'Cash on Delivery')
            ->assertJsonPath('0.items.0.name', 'Classic Tee');

        $this->actingAs($admin)
            ->getJson(route('admin.data.orders.show', $order->order_number))
            ->assertOk()
            ->assertJsonPath('id', $order->order_number);
    }

    public function test_admin_can_persist_order_status_and_tracking_details(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);
        $admin->is_admin = true;
        $admin->save();
        $order = Order::create([
            'order_number' => 'BW-261011-TEST5678',
            'customer_name' => 'Test Customer',
            'customer_phone' => '03001234567',
            'shipping_address' => 'House 1, Main Road',
            'city' => 'Lahore',
            'subtotal' => 2000,
            'discount' => 0,
            'shipping_fee' => 250,
            'total_amount' => 2250,
            'payment_method' => 'COD',
        ]);

        $this->actingAs($admin)
            ->putJson(route('admin.data.orders.update', $order->order_number), [
                'status' => 'Shipped',
                'tracking' => 'TCS 4001234567',
                'courier' => 'TCS',
                'adminNotes' => 'Packed and dispatched',
            ])
            ->assertOk()
            ->assertJsonPath('status', 'Shipped')
            ->assertJsonPath('tracking', 'TCS 4001234567');

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'order_status' => 'shipped',
            'shipping_status' => 'shipped',
            'tracking_number' => 'TCS 4001234567',
            'shipping_courier' => 'TCS',
            'admin_note' => 'Packed and dispatched',
        ]);

        $this->post(route('track-order.lookup'), [
            'order_number' => 'TCS4001234567',
            'customer_phone' => '+92 300 123 4567',
        ])
            ->assertOk()
            ->assertSee($order->order_number)
            ->assertSee('TCS 4001234567')
            ->assertSee('Shipping status:')
            ->assertSee('Shipping status: <strong>Shipped</strong>', false);

        $this->actingAs($admin)
            ->putJson(route('admin.data.orders.update', $order->order_number), [
                'status' => 'Processing',
            ])
            ->assertOk()
            ->assertJsonPath('status', 'Processing');

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'order_status' => 'processing',
            'shipping_status' => 'processing',
        ]);

        $this->post(route('track-order.lookup'), [
            'order_number' => $order->public_tracking_id,
            'customer_phone' => '03001234567',
        ])
            ->assertOk()
            ->assertSee('Processing')
            ->assertSee('Shipping status: <strong>Processing</strong>', false);
    }
}
