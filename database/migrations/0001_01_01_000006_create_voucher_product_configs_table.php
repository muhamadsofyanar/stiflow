<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('voucher_product_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->unique()->constrained('products')->cascadeOnDelete();
            $table->decimal('unit_price', 12, 2)->unsigned();
            $table->unsignedInteger('min_qty')->default(1);
            $table->unsignedInteger('max_qty')->default(100);
            $table->json('presets_json')->nullable();
            $table->json('free_units_rule_json')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('voucher_product_configs');
    }
};
