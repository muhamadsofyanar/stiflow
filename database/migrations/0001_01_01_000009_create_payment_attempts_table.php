<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->enum('method', ['manual_transfer', 'gateway'])->default('manual_transfer');
            $table->string('provider', 50)->nullable();
            $table->decimal('amount', 12, 2)->unsigned();
            $table->string('currency', 3)->default('IDR');
            $table->enum('status', ['pending', 'submitted', 'verified', 'rejected', 'failed', 'refunded'])->default('pending');
            $table->string('external_reference')->nullable();
            $table->string('reference_id_for_provider')->nullable();
            $table->json('provider_response_json')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->index(['order_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_attempts');
    }
};
