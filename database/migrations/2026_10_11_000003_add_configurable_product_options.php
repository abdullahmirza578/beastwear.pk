<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $productColumns = array_filter([
            ! Schema::hasColumn('products', 'design_selection_enabled') ? 'design_selection_enabled' : null,
            ! Schema::hasColumn('products', 'size_selection_enabled') ? 'size_selection_enabled' : null,
            ! Schema::hasColumn('products', 'color_selection_enabled') ? 'color_selection_enabled' : null,
        ]);
        if ($productColumns !== []) {
            Schema::table('products', function (Blueprint $table) use ($productColumns) {
                foreach ($productColumns as $column) {
                    $table->boolean($column)->default(false);
                }
            });
        }

        $imageColumns = array_filter([
            ! Schema::hasColumn('product_images', 'is_selectable_design') ? 'is_selectable_design' : null,
            ! Schema::hasColumn('product_images', 'design_name') ? 'design_name' : null,
        ]);
        if ($imageColumns !== []) {
            Schema::table('product_images', function (Blueprint $table) use ($imageColumns) {
                foreach ($imageColumns as $column) {
                    if ($column === 'is_selectable_design') {
                        $table->boolean($column)->default(false);
                    } else {
                        $table->string($column, 100)->nullable();
                    }
                }
            });
        }

        if (! Schema::hasColumn('cart_items', 'selected_design_image_id')) {
            Schema::table('cart_items', function (Blueprint $table) {
                $table->foreignId('selected_design_image_id')->nullable()
                    ->constrained('product_images')->nullOnDelete();
            });
        }
        if (! Schema::hasColumn('cart_items', 'selection_key')) {
            Schema::table('cart_items', function (Blueprint $table) {
                $table->string('selection_key', 80)->default('');
            });
        }

        $cartIndexes = collect(Schema::getIndexes('cart_items'))->pluck('name');
        if (! $cartIndexes->contains('cart_item_options_lookup')) {
            Schema::table('cart_items', function (Blueprint $table) {
                $table->index(['cart_id', 'product_id', 'selection_key'], 'cart_item_options_lookup');
            });
        }
        if ($cartIndexes->contains('cart_item_selection_unique')) {
            Schema::table('cart_items', function (Blueprint $table) {
                $table->dropUnique('cart_item_selection_unique');
            });
        }

        $orderItemColumns = array_filter([
            ! Schema::hasColumn('order_items', 'design_name') ? 'design_name' : null,
            ! Schema::hasColumn('order_items', 'design_image_path') ? 'design_image_path' : null,
        ]);
        if ($orderItemColumns !== []) {
            Schema::table('order_items', function (Blueprint $table) use ($orderItemColumns) {
                foreach ($orderItemColumns as $column) {
                    $table->string($column, $column === 'design_name' ? 100 : 255)->nullable();
                }
            });
        }

        DB::table('products')->orderBy('id')->each(function (object $product): void {
            $variants = DB::table('product_variants')->where('product_id', $product->id);
            DB::table('products')->where('id', $product->id)->update([
                'size_selection_enabled' => (clone $variants)->whereNotNull('size')->exists(),
                'color_selection_enabled' => (clone $variants)->whereNotNull('color')->exists(),
            ]);
        });

        DB::table('cart_items')->orderBy('id')->each(function (object $item): void {
            DB::table('cart_items')->where('id', $item->id)->update([
                'selection_key' => $this->selectionKey(
                    $item->product_variant_id,
                    $item->selected_design_image_id,
                ),
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['design_name', 'design_image_path']);
        });

        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropIndex('cart_item_options_lookup');
            $table->dropForeign(['selected_design_image_id']);
            $table->dropColumn(['selected_design_image_id', 'selection_key']);
            $table->unique(
                ['cart_id', 'product_id', 'product_variant_id'],
                'cart_item_selection_unique'
            );
        });

        Schema::table('product_images', function (Blueprint $table) {
            $table->dropColumn(['is_selectable_design', 'design_name']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'design_selection_enabled',
                'size_selection_enabled',
                'color_selection_enabled',
            ]);
        });
    }

    private function selectionKey(?int $variantId, ?int $designImageId): string
    {
        return 'v'.($variantId ?? 0).'-d'.($designImageId ?? 0);
    }
};
