<?php

namespace App\Services\Lms;

use App\Enums\AuditAction;
use App\Enums\EnrollmentSource;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\User;
use App\Services\Audit\AuditService;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class EnrollmentProgressService
{
    public function createEnrollment(
        User $user,
        Course $course,
        EnrollmentSource $source = EnrollmentSource::Manual,
        array $metadata = [],
    ): Enrollment {
        return DB::transaction(function () use ($user, $course, $source, $metadata) {
            $existing = Enrollment::query()
                ->where('user_id', $user->id)
                ->where('course_id', $course->id)
                ->first();

            if ($existing) {
                return $existing;
            }

            $enrollment = Enrollment::query()->create([
                'user_id' => $user->id,
                'course_id' => $course->id,
                'enrollment_source' => $source,
                'order_id' => $metadata['order_id'] ?? null,
                'granted_by_user_id' => $metadata['granted_by_user_id'] ?? null,
                'promoter_profile_id_granted' => $metadata['promoter_profile_id_granted'] ?? null,
                'is_active' => true,
                'expires_at' => $metadata['expires_at'] ?? null,
                'completed_at' => null,
                'progress_percent' => 0,
                'last_accessed_at' => now(),
            ]);

            AuditService::record(
                action: AuditAction::ProductCreated ?? 'enrollment.created',
                subject: $enrollment,
                after: [
                    'user_id' => $user->id,
                    'course_id' => $course->id,
                    'source' => $source->value,
                ],
                actor: $metadata['actor'] ?? null,
            );

            return $enrollment;
        });
    }

    public function markLessonComplete(Enrollment $enrollment, Lesson $lesson, int $secondsWatched = 0): LessonProgress
    {
        if ((int) $enrollment->course_id !== (int) $lesson->courseModule?->course_id) {
            throw new InvalidArgumentException('Lesson tidak termasuk dalam course enrollment.');
        }

        return DB::transaction(function () use ($enrollment, $lesson, $secondsWatched) {
            $progress = LessonProgress::query()
                ->where('enrollment_id', $enrollment->id)
                ->where('lesson_id', $lesson->id)
                ->where('user_id', $enrollment->user_id)
                ->first();

            $completed = null;
            $prevCompleted = false;

            if ($progress) {
                $prevCompleted = (bool) $progress->is_completed;
                $progress->seconds_watched = max((int) $progress->seconds_watched, $secondsWatched);
                if (! $progress->is_completed) {
                    $progress->is_completed = true;
                    $progress->completed_at = now();
                }
                $progress->save();
                $completed = $progress;
            } else {
                $completed = LessonProgress::query()->create([
                    'enrollment_id' => $enrollment->id,
                    'lesson_id' => $lesson->id,
                    'user_id' => $enrollment->user_id,
                    'is_completed' => true,
                    'seconds_watched' => $secondsWatched,
                    'started_at' => now(),
                    'completed_at' => now(),
                    'last_position_ratio' => 1.0,
                ]);
            }

            if (! $prevCompleted) {
                $this->recalculateProgress($enrollment);
            }

            return $completed;
        });
    }

    public function recalculateProgress(Enrollment $enrollment): Enrollment
    {
        return DB::transaction(function () use ($enrollment) {
            $totalLessons = Lesson::query()
                ->join('course_modules', 'course_modules.id', '=', 'lessons.course_module_id')
                ->where('course_modules.course_id', $enrollment->course_id)
                ->where('lessons.is_published', true)
                ->count();

            $completedCount = 0;
            if ($totalLessons > 0) {
                $completedIds = LessonProgress::query()
                    ->where('enrollment_id', $enrollment->id)
                    ->where('is_completed', true)
                    ->pluck('lesson_id')
                    ->all();

                if (! empty($completedIds)) {
                    $completedCount = Lesson::query()
                        ->join('course_modules', 'course_modules.id', '=', 'lessons.course_module_id')
                        ->where('course_modules.course_id', $enrollment->course_id)
                        ->whereIn('lessons.id', $completedIds)
                        ->count();
                }
            }

            $percent = $totalLessons > 0 ? (int) round(($completedCount / $totalLessons) * 100) : 0;

            $enrollment->progress_percent = $percent;
            $enrollment->last_accessed_at = now();

            if ($percent >= 100 && $enrollment->completed_at === null) {
                $enrollment->completed_at = now();
            }

            $enrollment->save();

            return $enrollment;
        });
    }
}
