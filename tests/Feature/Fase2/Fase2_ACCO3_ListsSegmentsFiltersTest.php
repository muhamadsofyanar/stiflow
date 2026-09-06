<?php

namespace Tests\Feature\Fase2;

use App\Enums\UserRole;
use App\Models\Contact;
use App\Models\ContactList;
use App\Models\Segment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Fase2_ACCO3_ListsSegmentsFiltersTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_lists_and_segments_200(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Admin, 'status' => 'active', 'email_verified_at' => now(),
        ]);

        ContactList::query()->create([
            'name' => 'List Pelanggan Aktif CO3',
            'description' => 'Daftar pelanggan yang masih aktif',
        ]);
        Segment::query()->create([
            'name' => 'Segment Domisili Jabodetabek',
            'filter_rules_json' => ['city' => 'Jakarta'],
        ]);

        $this->actingAs($admin)->get(route('admin.lists.index'))->assertStatus(200);
        $this->actingAs($admin)->get(route('admin.segments.index'))->assertStatus(200);
    }
}
