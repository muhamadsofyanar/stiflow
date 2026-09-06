<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use Illuminate\Contracts\View\View;

class CourseController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $user = auth()->user();
            $hasLinkedContact = \App\Models\Contact::query()->where('user_linked_id', $user->id)->exists();
            abort_unless($user->isPromotor() || $hasLinkedContact, 403);

            return $next($request);
        });
    }

    public function index(): View
    {
        $user = auth()->user();

        $enrollments = Enrollment::query()
            ->where('user_id', $user->id)
            ->with(['course.modules.lessons'])
            ->latest()
            ->paginate(15);

        return view('member.kelas.index', compact('enrollments'));
    }
}
