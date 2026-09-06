<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('update_channel_releases')) {
            Schema::create('update_channel_releases', function (Blueprint $table) {
                $table->id();
                $table->string('version_semver')->unique();
                $table->enum('channel', ['Stable', 'Preview'])->default('Stable');
                $table->longText('release_notes_markdown')->nullable();
                $table->boolean('is_critical')->default(false);
                $table->string('min_php_version')->nullable();
                $table->string('min_mysql_version')->nullable();
                $table->timestamp('published_at')->nullable();
                $table->string('rollback_target_version')->nullable();
                $table->json('changelog_json')->nullable();
                $table->json('install_instructions_json')->nullable();
                $table->unsignedInteger('file_size_mb')->default(0);
                $table->string('release_signature_sha256', 64)->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('update_channel_releases');
    }
};
