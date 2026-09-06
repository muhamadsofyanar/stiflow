<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CourseManagementController extends Controller
{
    public function index(): View
    {
        $courses = Course::query()->withCount(['modules', 'lessons'])->latest()->paginate(15);

        return view('admin.courses.index', compact('courses'));
    }

    public function create(): View
    {
        return view('admin.courses.create');
    }

    public function store(Request $request): RedirectResponse
    {
        Course::query()->create($request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:courses,slug',
            'description' => 'nullable|string',
            'product_id' => 'nullable|exists:products,id',
            'is_published' => 'boolean',
        ]));

        return redirect()->route('admin.courses.index')->with('status', 'Kursus dibuat.');
    }

    public function show(Course $course): View
    {
        $course->load(['modules.lessons']);

        return view('admin.courses.show', compact('course'));
    }

    public function edit(Course $course): View
    {
        return view('admin.courses.edit', compact('course'));
    }

    public function update(Request $request, Course $course): RedirectResponse
    {
        $course->update($request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:courses,slug,'.$course->id,
            'description' => 'nullable|string',
            'product_id' => 'nullable|exists:products,id',
            'is_published' => 'boolean',
        ]));

        return redirect()->route('admin.courses.index')->with('status', 'Kursus diperbarui.');
    }

    public function destroy(Course $course): RedirectResponse
    {
        $course->delete();

        return redirect()->route('admin.courses.index')->with('status', 'Kursus dihapus.');
    }
}
