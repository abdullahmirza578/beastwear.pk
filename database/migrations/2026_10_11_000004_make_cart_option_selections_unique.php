<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $duplicateLines = DB::table('cart_items')
            ->select('cart_id', 'product_id', 'selection_key', DB::raw('MIN(id) as keep_id'))
            ->groupBy('cart_id', 'product_id', 'selection_key')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicateLines as $duplicate) {
            $lineIds = DB::table('cart_items')
                ->where('cart_id', $duplicate->cart_id)
                ->where('product_id', $duplicate->product_id)
                ->where('selection_key', $duplicate->selection_key)
                ->pluck('id');
            $quantity = DB::table('cart_items')->whereIn('id', $lineIds)->sum('quantity');

            DB::table('cart_items')->where('id', $duplicate->keep_id)->update(['quantity' => $quantity]);
            DB::table('cart_items')->whereIn('id', $lineIds)->where('id', '!=', $duplicate->keep_id)->delete();
        }

        Schema::table('cart_items', function (Blueprint $table) {
            $table->unique(
                ['cart_id', 'product_id', 'selection_key'],
                'cart_item_options_unique'
            );
            $table->dropIndex('cart_item_options_lookup');
        });
    }

    public function down(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropUnique('cart_item_options_unique');
            $table->index(['cart_id', 'product_id', 'selection_key'], 'cart_item_options_lookup');
        });
    }
};
