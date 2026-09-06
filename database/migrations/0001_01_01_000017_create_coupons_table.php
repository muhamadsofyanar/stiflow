<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->enum('type', ['fixed', 'percent'])->default('fixed');
            $table->decimal('value', 12, 2)->unsigned();
            $table->decimal('max_discount', 12, 2)->unsigned()->nullable();
            $table->decimal('min_order_total', 12, 2)->unsigned()->default(0);
            $table->unsignedInteger('max_redemptions_global')->nullable();
            $table->unsignedInteger('max_redemptions_per_user')->default(1);
            $table->json('product_scope_json')->nullable();
            $table->json('user_scope_json')->nullable();
            $table->enum('status', ['active', 'inactive', 'expired'])->default('active');
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
