<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stifin_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('member_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('uploaded_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('promoter_owner_profile_id')->nullable()->constrained('promoter_profiles')->nullOnDelete();
            $table->string('name')->nullable();
            $table->string('result_type')->default('pro_stifin');
            $table->json('main_result_json')->nullable();
            $table->json('elements_json')->nullable();
            $table->text('summary_text')->nullable();
            $table->string('pdf_file_path')->nullable();
            $table->string('source_file_name')->nullable();
            $table->string('report_batch_number')->nullable()->index();
            $table->date('test_taken_date')->nullable();
            $table->date('valid_until_date')->nullable();
            $table->boolean('is_sensitive_locked')->default(false);
            $table->foreignId('access_granted_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('generated_at')->nullable();
            $table->json('linked_course_ids_json')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('digital_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('course_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('lesson_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('uploaded_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('asset_type')->default('file');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('storage_disk')->default('private');
            $table->string('storage_path')->unique();
            $table->string('original_filename')->nullable();
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('file_size_bytes')->nullable();
            $table->string('checksum_sha256', 64)->nullable()->index();
            $table->string('version')->default('1.0.0');
            $table->boolean('is_published')->default(true);
            $table->unsignedInteger('download_limit_default')->default(3);
            $table->unsignedInteger('expiry_hours_default')->default(168);
            $table->foreignId('replaces_asset_id')->nullable()->constrained('digital_assets')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('download_grants', function (Blueprint $table) {
            $table->id();
            $table->char('token', 64)->unique();
            $table->foreignId('digital_asset_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('order_item_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('enrollment_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('max_downloads')->default(3);
            $table->unsignedInteger('downloads_made_count')->default(0);
            $table->timestamp('expires_at');
            $table->timestamp('first_downloaded_at')->nullable();
            $table->timestamp('last_downloaded_at')->nullable();
            $table->string('asset_version_snapshot')->nullable();
            $table->string('checksum_snapshot')->nullable();
            $table->boolean('is_revoked')->default(false);
            $table->text('revocation_reason')->nullable();
            $table->ipAddress('ip_address_bound')->nullable();
            $table->string('user_agent_bound_fingerprint', 128)->nullable();
            $table->string('grant_source')->default('fulfillment');
            $table->timestamps();
        });

        Schema::create('asset_download_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('download_grant_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('digital_asset_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('token_used', 64)->nullable()->index();
            $table->ipAddress('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->boolean('success_flag')->default(true);
            $table->string('denial_reason')->nullable();
            $table->unsignedBigInteger('bytes_sent')->nullable();
            $table->timestamp('occurred_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_download_logs');
        Schema::dropIfExists('download_grants');
        Schema::dropIfExists('digital_assets');
        Schema::dropIfExists('stifin_results');
    }
};
