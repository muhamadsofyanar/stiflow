<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->json('product_snapshot_json');
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('unit_price', 12, 2)->unsigned()->default(0);
            $table->decimal('discount', 12, 2)->unsigned()->default(0);
            $table->decimal('total', 12, 2)->unsigned()->default(0);
            $table->enum('fulfillment_type', ['voucher', 'enrollment', 'digital', 'manual', 'license'])->default('voucher');
            $table->timestamps();

            $table->index(['order_id', 'fulfillment_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
