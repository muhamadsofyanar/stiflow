<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('landing_pages')) {
            Schema::create('landing_pages', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->string('slug')->unique();
                $table->enum('status', ['Draft', 'Published'])->default('Draft');
                $table->json('blocks_json')->nullable();
                $table->json('meta_json')->nullable();
                $table->timestamp('published_at')->nullable();
                $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('landing_page_visits')) {
            Schema::create('landing_page_visits', function (Blueprint $table) {
                $table->id();
                $table->foreignId('landing_page_id')->constrained('landing_pages')->cascadeOnDelete();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->text('referer')->nullable();
                $table->timestamp('visited_at')->useCurrent();
                $table->foreignId('contact_id')->nullable()->constrained('contacts')->nullOnDelete();
                $table->uuid('cookie_uuid')->nullable();
                $table->index(['landing_page_id', 'visited_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('landing_page_visits');
        Schema::dropIfExists('landing_pages');
    }
};
