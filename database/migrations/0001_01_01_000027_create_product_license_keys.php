<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_license_keys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            $table->text('encrypted_key_value');
            $table->string('fingerprint_sha256', 64)->unique();
            $table->string('status')->default('available')->index();
            $table->unsignedInteger('max_activations')->default(1);
            $table->unsignedInteger('activation_count')->default(0);
            $table->foreignId('assigned_to_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('assigned_from_order_item_id')->nullable()->constrained('order_items')->nullOnDelete();
            $table->foreignId('assigned_from_order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->timestamp('assigned_at')->nullable();
            $table->dateTime('expires_at')->nullable();
            $table->timestamp('suspended_at')->nullable();
            $table->text('suspension_reason')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->text('revocation_reason')->nullable();
            $table->string('import_batch_ref')->nullable()->index();
            $table->foreignId('imported_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->json('features_json')->nullable();
            $table->string('product_signing_secret_fingerprint', 64)->nullable();
            $table->timestamps();
        });

        Schema::create('product_license_activations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_license_key_id')->constrained()->cascadeOnDelete();
            $table->string('device_fingerprint_sha256', 64);
            $table->string('instance_label')->nullable();
            $table->string('ip_address_first_seen')->nullable();
            $table->text('hardware_info_json')->nullable();
            $table->string('hostname')->nullable();
            $table->string('os_name')->nullable();
            $table->string('app_version')->nullable();
            $table->unsignedInteger('activation_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamp('first_activated_at')->useCurrent();
            $table->timestamp('last_heartbeat_at')->nullable();
            $table->timestamp('deactivated_at')->nullable();
            $table->text('deactivation_reason')->nullable();
            $table->timestamps();
            $table->unique(['product_license_key_id', 'device_fingerprint_sha256'], 'pla_key_fingerprint_unique');
        });

        Schema::create('product_license_validation_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_license_key_id')->nullable()->constrained()->nullOnDelete();
            $table->string('fingerprint_given', 64)->nullable()->index();
            $table->string('device_fingerprint_given', 64)->nullable();
            $table->string('outcome');
            $table->text('rejection_reason')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('product_signature_valid')->nullable();
            $table->timestamp('occurred_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_license_validation_logs');
        Schema::dropIfExists('product_license_activations');
        Schema::dropIfExists('product_license_keys');
    }
};
