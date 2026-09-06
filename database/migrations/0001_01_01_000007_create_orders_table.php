<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->string('promotor_code_snapshot', 50)->nullable();
            $table->string('currency', 3)->default('IDR');
            $table->decimal('subtotal', 12, 2)->unsigned()->default(0);
            $table->decimal('discount', 12, 2)->unsigned()->default(0);
            $table->decimal('total', 12, 2)->unsigned()->default(0);
            $table->enum('status', [
                'pending_payment',
                'payment_submitted',
                'paid',
                'fulfilling',
                'completed',
                'needs_review',
                'rejected',
                'expired',
                'cancelled',
                'refunded',
            ])->default('pending_payment');
            $table->json('referral_snapshot_json')->nullable();
            $table->unsignedBigInteger('coupon_id')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
