<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartSelectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_delete_only_selected_cart_items(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        [$cart, $firstItem, $secondItem] = $this->cartWithTwoProducts($customer);
        $this->actingAs($customer)->get(route('cart'))->assertOk();

        $this->actingAs($customer)
            ->delete(route('cart.items.destroy-selected'), [
                'selected_items' => [$firstItem->id],
            ])
            ->assertRedirect(route('cart'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('cart_items', ['id' => $firstItem->id]);
        $this->assertDatabaseHas('cart_items', [
            'id' => $secondItem->id,
            'cart_id' => $cart->id,
        ]);
    }

    public function test_customer_cannot_delete_cart_items_owned_by_another_customer(): void
    {
        $owner = User::factory()->create(['role' => 'customer']);
        $attacker = User::factory()->create(['role' => 'customer']);
        [, $item] = $this->cartWithTwoProducts($owner);

        $this->actingAs($attacker)
            ->delete(route('cart.items.destroy-selected'), [
                'selected_items' => [$item->id],
            ])
            ->assertSessionHasErrors('selected_items');

        $this->assertDatabaseHas('cart_items', ['id' => $item->id]);
    }

    public function test_checkout_orders_selected_products_and_keeps_unselected_products_in_cart(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        [$cart, $selectedItem, $remainingItem] = $this->cartWithTwoProducts($customer);

        $this->actingAs($customer)
            ->get(route('checkout', ['items' => [$selectedItem->id]]))
            ->assertOk()
            ->assertViewHas('cart', fn (Cart $loadedCart): bool => $loadedCart->items->count() === 1
                && $loadedCart->items->first()->is($selectedItem))
            ->assertSee('name="cart_item_ids[]" value="'.$selectedItem->id.'"', false);

        $this->actingAs($customer)
            ->post(route('checkout.store'), [
                'customer_name' => $customer->name,
                'customer_phone' => '03001234567',
                'customer_email' => $customer->email,
                'shipping_address' => 'House 1, Main Road',
                'city' => 'Lahore',
                'payment_method' => 'COD',
                'cart_item_ids' => [$selectedItem->id],
            ])
            ->assertSessionHasNoErrors();

        $order = Order::where('user_id', $customer->id)->firstOrFail();
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_id' => $selectedItem->product_id,
        ]);
        $this->assertDatabaseMissing('order_items', [
            'order_id' => $order->id,
            'product_id' => $remainingItem->product_id,
        ]);
        $this->assertDatabaseMissing('cart_items', ['id' => $selectedItem->id]);
        $this->assertDatabaseHas('cart_items', [
            'id' => $remainingItem->id,
            'cart_id' => $cart->id,
        ]);
    }

    public function test_checkout_rejects_item_selection_from_another_cart(): void
    {
        $owner = User::factory()->create(['role' => 'customer']);
        $attacker = User::factory()->create(['role' => 'customer']);
        [, $item] = $this->cartWithTwoProducts($owner);

        $this->actingAs($attacker)
            ->get(route('checkout', ['items' => [$item->id]]))
            ->assertRedirect(route('cart'))
            ->assertSessionHasErrors('cart');
    }

    private function cartWithTwoProducts(User $customer): array
    {
        $category = Category::create([
            'name' => 'Cart Test Category',
            'slug' => 'cart-test-category-'.$customer->id,
            'status' => 'active',
        ]);
        $cart = $customer->cart()->create();
        $products = collect(['First Cart Product', 'Second Cart Product'])->map(function (string $name, int $index) use ($category): Product {
            return Product::create([
                'category_id' => $category->id,
                'name' => $name,
                'slug' => strtolower(str_replace(' ', '-', $name)),
                'sku' => 'CART-TEST-00'.($index + 1),
                'price' => 2000,
                'stock_quantity' => 10,
                'status' => 'active',
            ]);
        });
        $items = $products->map(fn (Product $product, int $index) => $cart->items()->create([
            'product_id' => $product->id,
            'selection_key' => 'v0-d0',
            'quantity' => 1,
            'price' => 2000,
        ]));

        return [$cart, $items[0]->load('product'), $items[1]->load('product')];
    }
}
