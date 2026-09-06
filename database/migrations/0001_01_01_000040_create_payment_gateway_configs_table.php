<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('payment_gateway_configs')) {
            Schema::create('payment_gateway_configs', function (Blueprint $table) {
                $table->id();
                $table->enum('provider', ['Xendit', 'Midtrans', 'Finpay', 'ManualBankTransfer'])->default('ManualBankTransfer');
                $table->string('display_name');
                $table->boolean('is_active')->default(false);
                $table->unsignedInteger('sort_order')->default(0);
                $table->json('credentials_json')->nullable();
                $table->text('webhook_url')->nullable();
                $table->json('supported_currencies_json')->nullable();
                $table->decimal('minimum_amount', 15, 2)->default(0);
                $table->decimal('maximum_amount', 15, 2)->default(0);
                $table->decimal('fixed_fee', 15, 2)->default(0);
                $table->decimal('percent_fee', 5, 2)->default(0);
                $table->text('instructions_markdown')->nullable();
                $table->json('meta_json')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_gateway_configs');
    }
};
