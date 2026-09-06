<?php

namespace Tests\Feature\Fase2;

use App\Enums\UserRole;
use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Fase2_LMS1_CourseManagementCRUDTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_course_crud_routes(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Admin, 'status' => 'active', 'email_verified_at' => now(),
        ]);

        $store = $this->actingAs($admin)->post(route('admin.courses.store'), [
            'title' => 'Kursus STIFIN Dasar',
            'slug' => 'stifin-dasar-lms1',
            'description' => 'Kursus dasar STIFIN.',
            'is_published' => true,
        ]);
        $store->assertRedirect(route('admin.courses.index'));
        $this->assertDatabaseHas('courses', ['title' => 'Kursus STIFIN Dasar']);

        $course = Course::query()->where('slug', 'stifin-dasar-lms1')->firstOrFail();

        $this->actingAs($admin)->get(route('admin.courses.index'))->assertStatus(200);
        $this->actingAs($admin)->get(route('admin.courses.show', $course))->assertStatus(200);

        $edit = $this->actingAs($admin)->put(route('admin.courses.update', $course), [
            'title' => 'Kursus STIFIN Dasar UPDATED',
            'slug' => 'stifin-dasar-lms1',
            'is_published' => true,
        ]);
        $edit->assertRedirect(route('admin.courses.index'));
        $this->assertSame('Kursus STIFIN Dasar UPDATED', $course->fresh()->title);
    }
}
