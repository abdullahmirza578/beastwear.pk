<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductReviewFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_review_delivered_purchased_product_and_admin_approval_publishes_it(): void
    {
        $customer = User::factory()->create(['name' => 'Review Customer']);
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $product = $this->product();
        $order = $this->order($customer, $product, 'delivered');

        $this->actingAs($customer)
            ->get(route('products.show', $product->slug))
            ->assertOk()
            ->assertSee('Write a Review')
            ->assertSee(route('reviews.store', $product));

        $this->actingAs($customer)
            ->post(route('reviews.store', $product), [
                'order_id' => $order->id,
                'rating' => 5,
                'comment' => 'Excellent quality and fit.',
            ])
            ->assertRedirect()
            ->assertSessionHas('status', 'Your review was submitted for moderation.');

        $review = Review::where('product_id', $product->id)->firstOrFail();
        $this->assertSame('pending', $review->status);
        $this->assertSame($customer->id, $review->user_id);

        $this->actingAs($admin)
            ->getJson(route('admin.reviews.data'))
            ->assertOk()
            ->assertJsonPath('0.customer', 'Review Customer')
            ->assertJsonPath('0.product', $product->name)
            ->assertJsonPath('0.status', 'Pending');

        $this->get(route('products.show', $product->slug))
            ->assertOk()
            ->assertDontSee('Excellent quality and fit.');

        $this->actingAs($admin)
            ->patchJson(route('admin.reviews.update', $review), ['status' => 'Approved'])
            ->assertOk()
            ->assertJsonPath('status', 'Approved');

        $this->get(route('products.show', $product->slug))
            ->assertOk()
            ->assertSee('Excellent quality and fit.')
            ->assertSee('Review Customer');
    }

    public function test_customer_cannot_review_a_product_without_their_own_delivered_order(): void
    {
        $customer = User::factory()->create();
        $otherCustomer = User::factory()->create();
        $product = $this->product();
        $order = $this->order($otherCustomer, $product, 'delivered');

        $this->actingAs($customer)
            ->post(route('reviews.store', $product), [
                'order_id' => $order->id,
                'rating' => 4,
                'comment' => 'Not my order',
            ])
            ->assertSessionHasErrors('order_id');

        $pendingOrder = $this->order($customer, $product, 'processing');
        $this->actingAs($customer)
            ->post(route('reviews.store', $product), [
                'order_id' => $pendingOrder->id,
                'rating' => 4,
            ])
            ->assertSessionHasErrors('order_id');

        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_guest_is_returned_to_product_after_signing_in_to_review(): void
    {
        $customer = User::factory()->create(['role' => 'customer', 'status' => 'active']);
        $product = $this->product();
        $productPath = '/product/'.$product->slug;
        $productUrl = route('products.show', $product->slug);

        $this->get(route('login', ['redirect' => $productPath]))
            ->assertOk();

        $this->post(route('login.store'), [
            'email' => $customer->email,
            'password' => 'password',
        ])->assertRedirect($productUrl);
    }

    public function test_review_is_pending_again_when_customer_edits_and_resubmits_it(): void
    {
        $customer = User::factory()->create();
        $product = $this->product();
        $order = $this->order($customer, $product, 'delivered');
        $review = Review::create([
            'user_id' => $customer->id,
            'product_id' => $product->id,
            'order_id' => $order->id,
            'rating' => 3,
            'comment' => 'Original review',
            'status' => 'approved',
        ]);

        $this->actingAs($customer)
            ->post(route('reviews.store', $product), [
                'order_id' => $order->id,
                'rating' => 5,
                'comment' => 'Updated review',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'rating' => 5,
            'comment' => 'Updated review',
            'status' => 'pending',
        ]);
    }

    private function product(): Product
    {
        $category = Category::create([
            'name' => 'T-Shirts',
            'slug' => 't-shirts',
            'status' => 'active',
        ]);

        return Product::create([
            'category_id' => $category->id,
            'name' => 'Review Test Tee',
            'slug' => 'review-test-tee',
            'sku' => 'REVIEW-TEST-001',
            'price' => 2000,
            'stock_quantity' => 10,
            'status' => 'active',
        ]);
    }

    private function order(User $customer, Product $product, string $status): Order
    {
        $order = Order::create([
            'user_id' => $customer->id,
            'order_number' => 'BW-REVIEW-'.strtoupper(bin2hex(random_bytes(5))),
            'customer_name' => $customer->name,
            'customer_phone' => '03001234567',
            'shipping_address' => 'House 1, Main Road',
            'city' => 'Lahore',
            'subtotal' => 2000,
            'discount' => 0,
            'shipping_fee' => 250,
            'total_amount' => 2250,
            'payment_method' => 'COD',
            'order_status' => $status,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'sku' => $product->sku,
            'quantity' => 1,
            'price' => 2000,
            'total' => 2000,
        ]);

        return $order;
    }
}
