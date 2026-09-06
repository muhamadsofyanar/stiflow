<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('integration_connections', function (Blueprint $table) {
            $table->id();
            $table->string('provider_category');
            $table->string('provider_type');
            $table->string('provider_name');
            $table->string('display_name');
            $table->longText('encrypted_credentials');
            $table->json('config_json')->nullable();
            $table->string('status')->default('configured');
            $table->text('last_error_message')->nullable();
            $table->timestamp('last_tested_at')->nullable();
            $table->timestamp('last_success_at')->nullable();
            $table->timestamp('degraded_since_at')->nullable();
            $table->timestamp('disabled_until_at')->nullable();
            $table->text('disabled_reason')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_primary')->default(false);
            $table->unsignedInteger('failure_count')->default(0);
            $table->unsignedInteger('circuit_breaker_threshold')->default(10);
            $table->unsignedTinyInteger('rate_limit_per_minute')->nullable();
            $table->foreignId('owned_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->ipAddress('allowed_server_ip')->nullable();
            $table->string('webhook_secret_fingerprint', 64)->nullable();
            $table->json('health_check_last_result_json')->nullable();
            $table->timestamps();
            $table->index(['provider_category', 'provider_type', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('integration_connections');
    }
};
