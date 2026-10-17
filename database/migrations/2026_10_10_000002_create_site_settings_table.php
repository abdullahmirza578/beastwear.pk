<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('store_name')->default('BeastWear.pk');
            $table->string('tagline')->nullable();
            $table->string('logo')->nullable();
            $table->string('favicon')->nullable();
            $table->string('email');
            $table->string('phone', 50);
            $table->string('whatsapp', 50);
            $table->text('address')->nullable();
            $table->string('instagram')->nullable();
            $table->string('facebook')->nullable();
            $table->string('tiktok')->nullable();
            $table->string('youtube')->nullable();
            $table->unsignedInteger('min_order')->default(1000);
            $table->string('order_confirmation', 30)->default('whatsapp');
            $table->boolean('cod_enabled')->default(true);
            $table->boolean('auto_confirm_prepaid')->default(true);
            $table->boolean('low_stock_alerts')->default(true);
            $table->boolean('maintenance')->default(false);
            $table->string('currency', 3)->default('PKR');
            $table->string('timezone', 50)->default('Asia/Karachi');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_settings');
    }
};
