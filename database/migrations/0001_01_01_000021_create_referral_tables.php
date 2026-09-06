<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('referral_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promoter_profile_id')->constrained()->cascadeOnDelete();
            $table->string('slug')->unique();
            $table->string('name')->nullable();
            $table->string('destination_path')->default('/lead-capture');
            $table->string('utm_source')->nullable();
            $table->string('utm_medium')->nullable();
            $table->string('utm_campaign')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('expires_at')->nullable();
            $table->unsignedBigInteger('total_visits')->default(0);
            $table->unsignedBigInteger('total_leads')->default(0);
            $table->timestamps();
        });

        Schema::create('referral_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referral_link_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('promoter_profile_id')->constrained()->cascadeOnDelete();
            $table->string('ip_hash', 128)->nullable()->index();
            $table->string('user_agent')->nullable();
            $table->string('landing_path')->nullable();
            $table->string('referrer_host')->nullable();
            $table->string('utm_source')->nullable();
            $table->string('utm_medium')->nullable();
            $table->string('utm_campaign')->nullable();
            $table->string('fingerprint', 128)->nullable()->index();
            $table->timestamp('converted_at')->nullable();
            $table->foreignId('converted_to_contact_id')->nullable()->constrained('contacts')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referrer_promoter_profile_id')->constrained('promoter_profiles')->cascadeOnDelete();
            $table->foreignId('referred_contact_id')->nullable()->constrained('contacts')->nullOnDelete();
            $table->foreignId('referred_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('referred_promoter_profile_id')->nullable()->constrained('promoter_profiles')->nullOnDelete();
            $table->string('status')->index();
            $table->foreignId('visit_id')->nullable()->constrained('referral_visits')->nullOnDelete();
            $table->timestamp('converted_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referrals');
        Schema::dropIfExists('referral_visits');
        Schema::dropIfExists('referral_links');
    }
};
