<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('public_tracking_id', 24)->nullable()->unique();
        });

        DB::table('orders')
            ->whereNull('public_tracking_id')
            ->orderBy('id')
            ->each(function (object $order): void {
                do {
                    $trackingId = 'BW-TRK-'.Str::upper(Str::random(12));
                } while (DB::table('orders')->where('public_tracking_id', $trackingId)->exists());

                DB::table('orders')
                    ->where('id', $order->id)
                    ->update(['public_tracking_id' => $trackingId]);
            });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique(['public_tracking_id']);
            $table->dropColumn('public_tracking_id');
        });
    }
};
