<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_proofs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_attempt_id')->unique()->constrained('payment_attempts')->cascadeOnDelete();
            $table->string('file_path');
            $table->string('original_filename')->nullable();
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('sender_name')->nullable();
            $table->string('sender_bank')->nullable();
            $table->decimal('transfer_amount', 12, 2)->unsigned()->nullable();
            $table->timestamp('transfer_time')->nullable();
            $table->text('review_note')->nullable();
            $table->enum('review_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['review_status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_proofs');
    }
};
