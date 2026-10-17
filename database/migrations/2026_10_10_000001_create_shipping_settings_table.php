<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_settings', function (Blueprint $table): void {
            $table->id();
            $table->boolean('nationwide')->default(true);
            $table->unsignedInteger('default_charge')->default(250);
            $table->unsignedInteger('free_threshold')->default(5000);
            $table->unsignedInteger('karachi_charge')->default(150);
            $table->unsignedInteger('other_city_charge')->default(250);
            $table->unsignedInteger('remote_area_charge')->default(350);
            $table->string('karachi_days', 50)->default('1-2');
            $table->string('other_days', 50)->default('3-5');
            $table->string('courier', 100)->default('Call Courier');
            $table->boolean('cod_enabled')->default(true);
            $table->unsignedInteger('cod_fee')->default(100);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_settings');
    }
};
