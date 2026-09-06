<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CourseManagementController extends Controller
{
    public function index(): View
    {
        $courses = Course::query()->withCount('modules')->latest()->paginate(15);

        return view('admin.courses.index', compact('courses'));
    }

    public function create(): View
    {
        $products = Product::query()->orderBy('name')->get(['id', 'name']);

        return view('admin.courses.create', compact('products'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:courses,slug',
            'summary' => 'nullable|string',
            'description' => 'nullable|string',
            'difficulty_level' => 'required|in:beginner,intermediate,advanced',
            'estimated_minutes' => 'nullable|integer|min:1',
            'linked_product_id' => 'nullable|exists:products,id',
            'is_free' => 'boolean',
            'is_published' => 'boolean',
        ]);

        Course::query()->create([
            ...$validated,
            'author_user_id' => $request->user()->id,
            'published_at' => ($validated['is_published'] ?? false) ? now() : null,
        ]);

        return redirect()->route('admin.courses.index')->with('status', 'Kursus dibuat.');
    }

    public function show(Course $course): View
    {
        $course->load(['modules.lessons']);

        return view('admin.courses.show', compact('course'));
    }

    public function edit(Course $course): View
    {
        $products = Product::query()->orderBy('name')->get(['id', 'name']);

        return view('admin.courses.edit', compact('course', 'products'));
    }

    public function update(Request $request, Course $course): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:courses,slug,'.$course->id,
            'summary' => 'nullable|string',
            'description' => 'nullable|string',
            'difficulty_level' => 'required|in:beginner,intermediate,advanced',
            'estimated_minutes' => 'nullable|integer|min:1',
            'linked_product_id' => 'nullable|exists:products,id',
            'is_free' => 'boolean',
            'is_published' => 'boolean',
        ]);

        $course->update([
            ...$validated,
            'published_at' => ($validated['is_published'] ?? false)
                ? ($course->published_at ?? now())
                : null,
        ]);

        return redirect()->route('admin.courses.index')->with('status', 'Kursus diperbarui.');
    }

    public function destroy(Course $course): RedirectResponse
    {
        $course->delete();

        return redirect()->route('admin.courses.index')->with('status', 'Kursus dihapus.');
    }
}
