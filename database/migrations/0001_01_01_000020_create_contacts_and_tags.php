<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->string('status')->index();
            $table->string('full_name');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email')->nullable()->index();
            $table->string('phone')->nullable()->index();
            $table->string('whatsapp')->nullable()->index();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('province')->nullable();
            $table->date('birthday')->nullable();
            $table->string('occupation')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('owner_promoter_profile_id')->nullable()->constrained('promoter_profiles')->nullOnDelete();
            $table->foreignId('owner_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('user_linked_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('stage_id')->nullable()->constrained('pipeline_stages')->nullOnDelete();
            $table->foreignId('pipeline_id')->nullable()->constrained('pipelines')->nullOnDelete();
            $table->decimal('estimated_value', 15, 2)->nullable();
            $table->unsignedInteger('score_points')->nullable();
            $table->timestamp('converted_at')->nullable();
            $table->date('next_followup_at')->nullable();
            $table->string('source_channel')->nullable();
            $table->foreignId('referred_by_promoter_profile_id')->nullable()->constrained('promoter_profiles')->nullOnDelete();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['owner_promoter_profile_id', 'status']);
            $table->index(['created_at']);
        });

        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('color_hex', 7)->nullable();
            $table->string('category')->nullable();
            $table->timestamps();
        });

        Schema::create('contact_tag', function (Blueprint $table) {
            $table->foreignId('contact_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
            $table->primary(['contact_id', 'tag_id']);
        });

        Schema::create('custom_fields', function (Blueprint $table) {
            $table->id();
            $table->string('model_type');
            $table->string('name');
            $table->string('key')->index();
            $table->string('field_type');
            $table->boolean('is_required')->default(false);
            $table->json('options')->nullable();
            $table->json('validation_rules')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
            $table->unique(['model_type', 'key']);
        });

        Schema::create('contact_custom_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_id')->constrained()->cascadeOnDelete();
            $table->foreignId('custom_field_id')->constrained()->cascadeOnDelete();
            $table->text('value')->nullable();
            $table->timestamps();
            $table->unique(['contact_id', 'custom_field_id']);
        });

        Schema::create('contact_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->text('content');
            $table->string('subject')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('done_at')->nullable();
            $table->string('outcome')->nullable();
            $table->timestamps();
        });

        Schema::create('crm_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('priority')->default('medium');
            $table->string('status')->default('pending');
            $table->dateTime('due_at')->nullable();
            $table->dateTime('remind_at')->nullable();
            $table->foreignId('assignee_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('related_deal_id')->nullable()->constrained('contacts')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_tasks');
        Schema::dropIfExists('contact_activities');
        Schema::dropIfExists('contact_custom_values');
        Schema::dropIfExists('custom_fields');
        Schema::dropIfExists('contact_tag');
        Schema::dropIfExists('tags');
        Schema::dropIfExists('contacts');
    }
};
