<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stifin_operations', function (Blueprint $table) {
            $table->id();
            $table->enum('operation_type', ['list_promoters', 'balance', 'add_voucher']);
            $table->string('request_reference', 150);
            $table->string('request_hash', 64)->nullable();
            $table->json('redacted_payload_json')->nullable();
            $table->longText('response_body_text')->nullable();
            $table->unsignedSmallInteger('http_status_code')->nullable();
            $table->enum('outcome', [
                'success',
                'fail_4xx',
                'fail_5xx',
                'ambiguous_timeout',
                'ambiguous_transport',
                'unparseable',
                'preflight_error',
            ])->nullable();
            $table->string('branch_code_snapshot', 50)->nullable();
            $table->string('promoter_code_snapshot', 50)->nullable();
            $table->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('fulfillment_id')->nullable()->constrained('fulfillments')->nullOnDelete();
            $table->timestamp('initiated_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['request_reference', 'operation_type']);
            $table->index(['outcome', 'created_at']);
            $table->index(['operation_type', 'promoter_code_snapshot']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stifin_operations');
    }
};
