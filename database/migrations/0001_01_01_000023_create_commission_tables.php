<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commission_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->unsignedTinyInteger('max_levels')->default(5);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->decimal('minimum_commission_per_entry', 15, 2)->default(0);
            $table->decimal('minimum_payout_total', 15, 2)->default(100000);
            $table->timestamps();
        });

        Schema::create('commission_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commission_plan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('product_type_filter')->nullable();
            $table->unsignedTinyInteger('level')->default(1);
            $table->string('rule_type');
            $table->decimal('value', 12, 4);
            $table->decimal('cap_per_unit_max', 15, 2)->nullable();
            $table->timestamps();
            $table->unique(['commission_plan_id', 'product_id', 'level']);
        });

        Schema::create('order_referral_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('direct_promoter_profile_id')->nullable()->constrained('promoter_profiles')->nullOnDelete();
            $table->json('ancestry_promoters_json');
            $table->foreignId('commission_plan_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('snapshot_at');
            $table->timestamps();
        });

        Schema::create('commission_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('beneficiary_promoter_profile_id')->constrained('promoter_profiles')->cascadeOnDelete();
            $table->unsignedTinyInteger('level');
            $table->string('status')->index();
            $table->decimal('amount', 15, 2);
            $table->decimal('amount_paid', 15, 2)->default(0);
            $table->decimal('rate_value', 12, 4);
            $table->string('rate_type');
            $table->foreignId('commission_plan_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('commission_rule_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('reversed_from_entry_id')->nullable()->constrained('commission_entries')->nullOnDelete();
            $table->foreignId('payout_id')->nullable()->constrained()->nullOnDelete();
            $table->text('rejection_reason')->nullable();
            $table->timestamp('earned_at')->nullable();
            $table->timestamp('locked_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('reversed_at')->nullable();
            $table->string('reference_id')->nullable()->index();
            $table->timestamps();
            $table->unique(['order_item_id', 'beneficiary_promoter_profile_id', 'level'], 'ce_item_beneficiary_level_unique');
        });

        Schema::create('payouts', function (Blueprint $table) {
            $table->id();
            $table->string('batch_number')->unique();
            $table->foreignId('commission_plan_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status')->index();
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->unsignedInteger('entry_count')->default(0);
            $table->foreignId('prepared_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('approved_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('paid_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('payment_method')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('bank_account_name')->nullable();
            $table->text('notes')->nullable();
            $table->text('admin_rejection_notes')->nullable();
            $table->timestamp('locked_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('reversed_at')->nullable();
            $table->date('period_start_date')->nullable();
            $table->date('period_end_date')->nullable();
            $table->timestamps();
        });

        Schema::create('payout_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payout_id')->constrained()->cascadeOnDelete();
            $table->foreignId('beneficiary_promoter_profile_id')->constrained('promoter_profiles')->cascadeOnDelete();
            $table->decimal('amount', 15, 2);
            $table->unsignedInteger('entries_count')->default(0);
            $table->timestamps();
        });

        Schema::table('payment_proofs', function (Blueprint $table) {
            $table->string('proofable_type')->nullable()->after('payment_attempt_id');
            $table->unsignedBigInteger('proofable_id')->nullable()->after('proofable_type');
            $table->index(['proofable_type', 'proofable_id']);
        });
    }

    public function down(): void
    {
        Schema::table('payment_proofs', function (Blueprint $table) {
            $table->dropMorphs('proofable');
        });
        Schema::dropIfExists('payout_items');
        Schema::dropIfExists('payouts');
        Schema::dropIfExists('commission_entries');
        Schema::dropIfExists('order_referral_snapshots');
        Schema::dropIfExists('commission_rules');
        Schema::dropIfExists('commission_plans');
    }
};
