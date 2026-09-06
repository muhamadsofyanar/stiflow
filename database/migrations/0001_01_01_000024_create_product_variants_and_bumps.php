<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'points_eligible')) {
                $table->boolean('points_eligible')->default(false)->after('commission_eligible');
            }
            if (!Schema::hasColumn('products', 'points_earn_rate_per_100k_price')) {
                $table->unsignedInteger('points_earn_rate_per_100k_price')->default(0)->after('points_eligible');
            }
            if (!Schema::hasColumn('products', 'is_points_redeemable')) {
                $table->boolean('is_points_redeemable')->default(false)->after('points_earn_rate_per_100k_price');
            }
            if (!Schema::hasColumn('products', 'points_redeem_cost_required')) {
                $table->unsignedInteger('points_redeem_cost_required')->default(0)->after('is_points_redeemable');
            }
            if (!Schema::hasColumn('products', 'has_variants')) {
                $table->boolean('has_variants')->default(false)->after('points_redeem_cost_required');
            }
            if (!Schema::hasColumn('products', 'features_json')) {
                $table->json('features_json')->nullable()->after('has_variants');
            }
            if (!Schema::hasColumn('products', 'long_description')) {
                $table->text('long_description')->nullable()->after('features_json');
            }
            if (!Schema::hasColumn('products', 'min_quantity_per_order')) {
                $table->unsignedInteger('min_quantity_per_order')->default(1);
            }
            if (!Schema::hasColumn('products', 'max_quantity_per_order')) {
                $table->unsignedInteger('max_quantity_per_order')->default(999);
            }
        });

        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('sku')->index();
            $table->decimal('price_override', 15, 2)->nullable();
            $table->unsignedInteger('stock_qty')->nullable();
            $table->boolean('in_stock')->default(true);
            $table->json('attributes_json')->nullable();
            $table->unsignedInteger('weight_gram')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->string('license_activation_limit')->nullable();
            $table->string('license_expiry_days')->nullable();
            $table->timestamps();
            $table->unique(['product_id', 'sku']);
        });

        Schema::create('order_bumps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('primary_product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('bump_product_id')->constrained('products')->cascadeOnDelete();
            $table->string('label');
            $table->text('description')->nullable();
            $table->decimal('discount_price', 15, 2)->nullable();
            $table->decimal('discount_percent', 5, 2)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->foreignId('product_variant_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('is_order_bump_snapshot')->default(false);
            $table->foreignId('parent_order_item_id')->nullable()->constrained('order_items')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign(['product_variant_id']);
            $table->dropForeign(['parent_order_item_id']);
            $table->dropColumn(['product_variant_id', 'is_order_bump_snapshot', 'parent_order_item_id']);
        });
        Schema::dropIfExists('order_bumps');
        Schema::dropIfExists('product_variants');
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'commission_eligible',
                'points_eligible',
                'points_earn_rate_per_100k_price',
                'is_points_redeemable',
                'points_redeem_cost_required',
                'has_variants',
                'features_json',
                'long_description',
            ]);
        });
    }
};
