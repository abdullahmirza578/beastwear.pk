<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table): void {
            $table->string('bank_name')->nullable();
            $table->string('bank_account_title')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('jazzcash_account_title')->nullable();
            $table->string('jazzcash_account_number')->nullable();
            $table->string('easypaisa_account_title')->nullable();
            $table->string('easypaisa_account_number')->nullable();
        });

        Schema::table('payments', function (Blueprint $table): void {
            $table->string('proof_path')->nullable()->after('payment_details');
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table): void {
            $table->dropColumn([
                'bank_name',
                'bank_account_title',
                'bank_account_number',
                'jazzcash_account_title',
                'jazzcash_account_number',
                'easypaisa_account_title',
                'easypaisa_account_number',
            ]);
        });

        Schema::table('payments', function (Blueprint $table): void {
            $table->dropColumn('proof_path');
        });
    }
};
