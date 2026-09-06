<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('application_licenses')) {
            Schema::create('application_licenses', function (Blueprint $table) {
                $table->id();
                $table->uuid('installation_uuid')->unique();
                $table->string('license_key_sha256', 64)->unique();
                $table->string('domain_name')->nullable();
                $table->string('customer_name')->nullable();
                $table->string('customer_email')->nullable();
                $table->enum('tier', ['Starter', 'Pro', 'Enterprise'])->default('Starter');
                $table->unsignedInteger('seats_allowed')->default(1);
                $table->unsignedInteger('max_branches')->default(1);
                $table->timestamp('activated_at')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->timestamp('last_validated_at')->nullable();
                $table->json('validation_signed_lease_json')->nullable();
                $table->timestamp('lease_valid_until')->nullable();
                $table->enum('status', ['Active', 'GracePeriod', 'Expired', 'Suspended', 'Revoked'])->default('Active');
                $table->text('suspension_reason')->nullable();
                $table->text('revocation_reason')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('application_licenses');
    }
};
