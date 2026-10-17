<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\ShippingMethod;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductOptionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_eight_product_option_combinations_validate_cart_and_checkout_selections(): void
    {
        $category = Category::create([
            'name' => 'T-Shirts',
            'slug' => 't-shirts',
            'status' => 'active',
        ]);
        SiteSetting::current()->update(['min_order' => 0]);
        ShippingMethod::create([
            'name' => 'Standard',
            'price' => 250,
            'estimated_days' => 3,
            'status' => 'active',
        ]);
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);

        for ($mask = 0; $mask < 8; $mask++) {
            $designEnabled = (bool) ($mask & 1);
            $sizeEnabled = (bool) ($mask & 2);
            $colorEnabled = (bool) ($mask & 4);
            $suffix = str_pad((string) $mask, 2, '0', STR_PAD_LEFT);
            $product = Product::create([
                'category_id' => $category->id,
                'name' => "Option Tee {$suffix}",
                'slug' => "option-tee-{$suffix}",
                'sku' => "OPTION-TEE-{$suffix}",
                'price' => 2000,
                'stock_quantity' => 20,
                'status' => 'active',
                'design_selection_enabled' => $designEnabled,
                'size_selection_enabled' => $sizeEnabled,
                'color_selection_enabled' => $colorEnabled,
            ]);
            $design = null;
            if ($designEnabled) {
                $design = ProductImage::create([
                    'product_id' => $product->id,
                    'image' => "products/design-{$suffix}.jpg",
                    'is_selectable_design' => true,
                    'design_name' => "Design {$suffix}",
                ]);
            }

            $sizes = $sizeEnabled ? ['S', 'M'] : [null];
            $colors = $colorEnabled ? ['Black', 'Maroon'] : [null];
            foreach ($sizes as $size) {
                foreach ($colors as $color) {
                    ProductVariant::create([
                        'product_id' => $product->id,
                        'size' => $size,
                        'color' => $color,
                        'stock_quantity' => 5,
                    ]);
                }
            }

            $customer = User::factory()->create();
            $requiredOptions = array_values(array_filter([
                $sizeEnabled ? 'size' : null,
                $colorEnabled ? 'color' : null,
                $designEnabled ? 'design_image_id' : null,
            ]));
            if ($requiredOptions !== []) {
                foreach ($requiredOptions as $missingOption) {
                    $partialSelection = [
                        'product_id' => $product->id,
                        'quantity' => 1,
                    ];
                    if ($sizeEnabled && $missingOption !== 'size') {
                        $partialSelection['size'] = 'S';
                    }
                    if ($colorEnabled && $missingOption !== 'color') {
                        $partialSelection['color'] = 'Black';
                    }
                    if ($designEnabled && $missingOption !== 'design_image_id') {
                        $partialSelection['design_image_id'] = $design->id;
                    }
                    $this->actingAs($customer)->post(route('cart.items.store'), $partialSelection)
                        ->assertSessionHasErrors($missingOption);
                    $this->assertFalse($customer->cart()->exists());
                }
            } else {
                $this->actingAs($customer)->post(route('cart.items.store'), [
                    'product_id' => $product->id,
                    'quantity' => 1,
                ])->assertSessionHasNoErrors();
                $this->assertSame(1, $customer->cart()->firstOrFail()->items()->count());
            }

            $selection = [
                'product_id' => $product->id,
                'quantity' => 1,
            ];
            if ($sizeEnabled) {
                $selection['size'] = 'S';
            }
            if ($colorEnabled) {
                $selection['color'] = 'Black';
            }
            if ($designEnabled) {
                $selection['design_image_id'] = $design->id;
            }

            $this->actingAs($customer)->post(route('cart.items.store'), $selection)
                ->assertSessionHasNoErrors();
            $cartItem = $customer->cart()->firstOrFail()->items()->firstOrFail();
            $this->assertSame($design?->id, $cartItem->selected_design_image_id);
            $this->assertSame($sizeEnabled ? 'S' : null, $cartItem->variant?->size);
            $this->assertSame($colorEnabled ? 'Black' : null, $cartItem->variant?->color);

            if ($sizeEnabled || $colorEnabled) {
                $secondSelection = $selection;
                $secondSelection['size'] = $sizeEnabled ? 'M' : null;
                $secondSelection['color'] = $colorEnabled ? 'Maroon' : null;
                $this->actingAs($customer)->post(route('cart.items.store'), $secondSelection)
                    ->assertSessionHasNoErrors();
                $this->assertSame(2, $customer->cart()->firstOrFail()->items()->count());
            }

            $this->actingAs($customer)->post(route('checkout.store'), [
                'customer_name' => $customer->name,
                'customer_phone' => '03001234567',
                'customer_email' => $customer->email,
                'shipping_address' => 'House 1, Main Road',
                'city' => 'Lahore',
                'payment_method' => 'COD',
            ])->assertSessionHasNoErrors();

            $order = Order::where('user_id', $customer->id)->latest()->firstOrFail();
            $orderItems = $order->items()->orderBy('id')->get();
            $this->assertCount($sizeEnabled || $colorEnabled ? 2 : 1, $orderItems);
            $this->assertEqualsCanonicalizing(
                $sizeEnabled ? ['S', 'M'] : array_fill(0, $orderItems->count(), null),
                $orderItems->pluck('size')->all()
            );
            $this->assertEqualsCanonicalizing(
                $colorEnabled ? ['Black', 'Maroon'] : array_fill(0, $orderItems->count(), null),
                $orderItems->pluck('color')->all()
            );
            foreach ($orderItems as $orderItem) {
                $this->assertSame($designEnabled ? "Design {$suffix}" : null, $orderItem->design_name);
                $this->assertSame(
                    $designEnabled ? "products/design-{$suffix}.jpg" : null,
                    $orderItem->design_image_path
                );
            }
            $adminOrder = $this->actingAs($admin)
                ->getJson(route('admin.data.orders.show', $order->order_number))
                ->assertOk()
                ->json();
            $this->assertEqualsCanonicalizing(
                $designEnabled ? ["Design {$suffix}"] : [null],
                collect($adminOrder['items'])->pluck('designName')->unique()->values()->all()
            );
            $this->assertEqualsCanonicalizing(
                $sizeEnabled ? ['S', 'M'] : ['—'],
                collect($adminOrder['items'])->pluck('size')->unique()->values()->all()
            );
            $this->assertEqualsCanonicalizing(
                $colorEnabled ? ['Black', 'Maroon'] : ['—'],
                collect($adminOrder['items'])->pluck('color')->unique()->values()->all()
            );
            $this->actingAs($customer);
        }
    }

    public function test_design_choices_create_distinct_lines_reject_forged_ids_and_reach_admin_order_details(): void
    {
        SiteSetting::current()->update(['min_order' => 0]);
        ShippingMethod::create([
            'name' => 'Standard',
            'price' => 250,
            'estimated_days' => 3,
            'status' => 'active',
        ]);
        $category = Category::create([
            'name' => 'Printed Tees',
            'slug' => 'printed-tees',
            'status' => 'active',
        ]);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Design Selection Tee',
            'slug' => 'design-selection-tee',
            'sku' => 'DESIGN-SELECTION-TEE',
            'price' => 2000,
            'stock_quantity' => 5,
            'status' => 'active',
            'design_selection_enabled' => true,
        ]);
        $designs = collect(['Sunset', 'Mountain'])->map(fn (string $name) => ProductImage::create([
            'product_id' => $product->id,
            'image' => 'products/'.strtolower($name).'.jpg',
            'is_selectable_design' => true,
            'design_name' => $name,
        ]));
        $otherProduct = Product::create([
            'category_id' => $category->id,
            'name' => 'Other Tee',
            'slug' => 'other-tee',
            'sku' => 'OTHER-TEE',
            'price' => 2000,
            'stock_quantity' => 5,
            'status' => 'active',
        ]);
        $forgedDesign = ProductImage::create([
            'product_id' => $otherProduct->id,
            'image' => 'products/not-allowed.jpg',
            'is_selectable_design' => true,
            'design_name' => 'Not Allowed',
        ]);
        $customer = User::factory()->create();

        $this->actingAs($customer)->post(route('cart.items.store'), [
            'product_id' => $product->id,
            'design_image_id' => $forgedDesign->id,
            'quantity' => 1,
        ])->assertSessionHasErrors('design_image_id');
        $this->assertFalse($customer->cart()->exists());

        foreach ($designs as $design) {
            $this->actingAs($customer)->post(route('cart.items.store'), [
                'product_id' => $product->id,
                'design_image_id' => $design->id,
                'quantity' => 1,
            ])->assertSessionHasNoErrors();
        }
        $this->assertSame(2, $customer->cart()->firstOrFail()->items()->count());

        $this->actingAs($customer)->post(route('checkout.store'), [
            'customer_name' => $customer->name,
            'customer_phone' => '03001234567',
            'customer_email' => $customer->email,
            'shipping_address' => 'House 1, Main Road',
            'city' => 'Lahore',
            'payment_method' => 'COD',
        ])->assertSessionHasNoErrors();
        $order = Order::where('user_id', $customer->id)->latest()->firstOrFail();
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $adminOrder = $this->actingAs($admin)
            ->getJson(route('admin.data.orders.show', $order->order_number))
            ->assertOk()
            ->json();

        $this->assertEqualsCanonicalizing(
            ['Sunset', 'Mountain'],
            collect($adminOrder['items'])->pluck('designName')->all()
        );
        $this->assertEqualsCanonicalizing(
            ['products/sunset.jpg', 'products/mountain.jpg'],
            $order->items()->pluck('design_image_path')->all()
        );
    }

    public function test_admin_can_save_independent_options_and_upload_selectable_designs(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $category = Category::create([
            'name' => 'Hoodies',
            'slug' => 'hoodies',
            'status' => 'active',
        ]);

        $this->actingAs($admin)->post(route('admin.products.store'), [
            'name' => 'Designable Hoodie',
            'sku' => 'DESIGN-HOODIE-1',
            'category_id' => $category->id,
            'price' => 3000,
            'stock_quantity' => 10,
            'status' => 'active',
            'design_selection_enabled' => '1',
            'size_selection_enabled' => '1',
            'color_selection_enabled' => '0',
            'variant_sizes' => 'S, M',
            'variant_colors' => '',
            'variant_stock' => 5,
            'design_images' => [UploadedFile::fake()->create('flame-print.jpg', 50, 'image/jpeg')],
        ])->assertRedirect(route('admin.products.index'));

        $product = Product::where('sku', 'DESIGN-HOODIE-1')->firstOrFail();
        $this->assertTrue($product->design_selection_enabled);
        $this->assertTrue($product->size_selection_enabled);
        $this->assertFalse($product->color_selection_enabled);
        $this->assertDatabaseHas('product_images', [
            'product_id' => $product->id,
            'is_selectable_design' => true,
            'design_name' => 'flame print',
        ]);
        $this->assertSame(2, $product->variants()->count());
    }
}
