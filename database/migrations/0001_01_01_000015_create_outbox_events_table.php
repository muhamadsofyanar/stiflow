<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('outbox_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_type', 100);
            $table->string('aggregate_type', 100)->nullable();
            $table->unsignedBigInteger('aggregate_id')->nullable();
            $table->json('payload_json')->nullable();
            $table->string('correlation_id', 100)->nullable()->index();
            $table->timestamp('available_at');
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('dispatched_at')->nullable();
            $table->string('job_uuid', 64)->nullable()->unique();
            $table->string('worker_result', 50)->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['processed_at', 'available_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('outbox_events');
    }
};
