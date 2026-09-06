<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('point_ledger_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('entry_type');
            $table->string('direction', 4);
            $table->bigInteger('amount_points');
            $table->bigInteger('balance_after_points')->nullable();
            $table->string('reason_code')->nullable();
            $table->text('reason_text')->nullable();
            $table->foreignId('related_order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->foreignId('related_order_item_id')->nullable()->constrained('order_items')->nullOnDelete();
            $table->foreignId('related_promoter_profile_id')->nullable()->constrained('promoter_profiles')->nullOnDelete();
            $table->string('reference_id')->nullable()->index();
            $table->foreignId('reversed_from_entry_id')->nullable()->constrained('point_ledger_entries')->nullOnDelete();
            $table->timestamp('expires_at')->nullable();
            $table->foreignId('performed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->json('meta_json')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'entry_type', 'created_at']);
            $table->unique(['reference_id', 'entry_type', 'user_id'], 'ple_ref_type_user_unique');
        });

        Schema::create('point_redemption_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reward_product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->bigInteger('points_used');
            $table->decimal('discount_applied_money', 15, 2)->default(0);
            $table->string('status')->index();
            $table->foreignId('applied_to_order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->foreignId('point_ledger_entry_id')->nullable()->constrained('point_ledger_entries')->nullOnDelete();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('point_redemption_orders');
        Schema::dropIfExists('point_ledger_entries');
    }
};
