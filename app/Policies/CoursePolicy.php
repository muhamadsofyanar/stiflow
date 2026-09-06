<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Course;
use App\Models\User;

class CoursePolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->role === UserRole::Admin) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Course $course): bool
    {
        if ($course->is_published) {
            if ($course->is_free) {
                return true;
            }

            return (bool) $user;
        }

        if ($user->isStaff()) {
            return $user->hasPermission('courses.view_all');
        }

        if ($user->isPromotor()) {
            return $course->author_user_id === null || (int) $course->author_user_id === (int) $user->id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isStaff() && $user->hasPermission('courses.manage');
    }

    public function update(User $user, Course $course): bool
    {
        if ($user->isStaff()) {
            return $user->hasPermission('courses.manage');
        }

        if ($user->isPromotor()) {
            return (int) $course->author_user_id === (int) $user->id;
        }

        return false;
    }

    public function manage(User $user, ?Course $course = null): bool
    {
        if ($user->isStaff()) {
            return $user->hasPermission('courses.manage');
        }

        if ($user->isPromotor()) {
            if ($course === null) {
                return false;
            }

            return (int) $course->author_user_id === (int) $user->id;
        }

        return false;
    }
}
