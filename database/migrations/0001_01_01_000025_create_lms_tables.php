<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('summary')->nullable();
            $table->longText('description')->nullable();
            $table->unsignedBigInteger('cover_image_id')->nullable();
            $table->string('difficulty_level')->default('beginner');
            $table->unsignedInteger('estimated_minutes')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->boolean('is_free')->default(false);
            $table->foreignId('linked_product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->foreignId('author_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->json('meta_tags')->nullable();
            $table->string('stifin_result_mapping_json')->nullable();
            $table->boolean('is_archived')->default(false);
            $table->timestamps();
        });

        Schema::create('course_modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('position')->default(0);
            $table->string('title');
            $table->text('summary')->nullable();
            $table->boolean('is_unlocked_by_default')->default(true);
            $table->timestamps();
        });

        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_module_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('position')->default(0);
            $table->string('title');
            $table->string('lesson_type')->default('text');
            $table->longText('content_text')->nullable();
            $table->string('video_url')->nullable();
            $table->string('video_provider')->nullable();
            $table->unsignedInteger('duration_minutes')->nullable();
            $table->boolean('is_preview_allowed')->default(false);
            $table->boolean('is_published')->default(true);
            $table->unsignedBigInteger('digital_asset_id')->nullable();
            $table->foreignId('quiz_id')->nullable()->nullOnDelete();
            $table->boolean('require_quiz_pass')->default(false);
            $table->unsignedInteger('min_quiz_score_percent')->nullable();
            $table->json('custom_fields_json')->nullable();
            $table->timestamps();
        });

        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->string('enrollment_source');
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('granted_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('promoter_profile_id_granted')->nullable()->constrained('promoter_profiles')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->decimal('progress_percent', 5, 2)->default(0);
            $table->timestamp('last_accessed_at')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'course_id', 'enrollment_source'], 'uniq_enroll_user_course_src_partial');
            $table->index(['user_id', 'is_active']);
        });

        Schema::create('lesson_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lesson_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_completed')->default(false);
            $table->unsignedInteger('seconds_watched')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->decimal('last_position_ratio', 5, 2)->nullable();
            $table->timestamps();
            $table->unique(['enrollment_id', 'lesson_id']);
        });

        Schema::create('quizzes', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedInteger('passing_percent')->default(70);
            $table->unsignedInteger('max_attempts')->default(3);
            $table->unsignedInteger('time_limit_minutes')->nullable();
            $table->boolean('shuffle_questions')->default(true);
            $table->boolean('show_correct_answer_after_submit')->default(true);
            $table->timestamps();
        });

        Schema::create('quiz_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('position')->default(0);
            $table->text('question_text');
            $table->string('question_type')->default('single_choice');
            $table->json('options_json');
            $table->json('correct_answer_json');
            $table->unsignedInteger('points')->default(1);
            $table->text('explanation')->nullable();
            $table->timestamps();
        });

        Schema::create('quiz_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lesson_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('enrollment_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('attempt_number')->default(1);
            $table->timestamp('started_at');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->decimal('score_percent', 5, 2)->nullable();
            $table->unsignedInteger('total_questions')->default(0);
            $table->unsignedInteger('correct_count')->default(0);
            $table->boolean('is_passed')->nullable();
            $table->json('answers_json')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'quiz_id', 'attempt_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_attempts');
        Schema::dropIfExists('quiz_questions');
        Schema::dropIfExists('quizzes');
        Schema::dropIfExists('lesson_progress');
        Schema::dropIfExists('enrollments');
        Schema::dropIfExists('lessons');
        Schema::dropIfExists('course_modules');
        Schema::dropIfExists('courses');
    }
};
