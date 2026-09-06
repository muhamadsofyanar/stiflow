<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fulfillments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_item_id')->constrained('order_items')->cascadeOnDelete();
            $table->enum('type', ['voucher', 'enrollment', 'digital', 'manual', 'license'])->default('voucher');
            $table->enum('status', ['pending', 'processing', 'success', 'failed', 'needs_review'])->default('pending');
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->string('correlation_id')->unique();
            $table->json('payload_snapshot_json')->nullable();
            $table->json('response_snapshot_json')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['order_item_id', 'type']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fulfillments');
    }
};
