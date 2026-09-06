<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('voucher_fulfillments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fulfillment_id')->unique()->constrained('fulfillments')->cascadeOnDelete();
            $table->foreignId('promoter_profile_id')->constrained('promoter_profiles')->restrictOnDelete();
            $table->string('stifin_code_snapshot', 50);
            $table->unsignedInteger('paid_units')->default(0);
            $table->unsignedInteger('free_units')->default(0);
            $table->unsignedInteger('balance_before_paid')->nullable();
            $table->unsignedInteger('balance_before_free')->nullable();
            $table->unsignedInteger('balance_after_paid')->nullable();
            $table->unsignedInteger('balance_after_free')->nullable();
            $table->string('stifin_reference', 100)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('voucher_fulfillments');
    }
};
