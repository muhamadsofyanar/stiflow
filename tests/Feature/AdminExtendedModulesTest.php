<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Database\Seeders\ProductionBootstrapSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminExtendedModulesTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create([
            'role' => UserRole::Admin,
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
    }

    public function test_admin_extended_module_pages_render_from_a_fresh_database(): void
    {
        $this->seed(ProductionBootstrapSeeder::class);

        $admin = $this->admin();

        foreach ([
            'admin.courses.index',
            'admin.products-catalog.index',
            'admin.stifin-results.index',
            'admin.campaigns.index',
            'admin.pipelines.index',
            'admin.integrations.index',
            'admin.points-ledger.index',
            'admin.staff.permissions',
            'admin.audit.index',
        ] as $routeName) {
            $this->actingAs($admin)->get(route($routeName))->assertOk();
        }
    }

    public function test_admin_extended_module_create_forms_render(): void
    {
        $this->seed(ProductionBootstrapSeeder::class);
        $admin = $this->admin();

        foreach ([
            'admin.courses.create',
            'admin.products-catalog.create',
            'admin.stifin-results.create',
            'admin.campaigns.create',
            'admin.pipelines.create',
            'admin.integrations.create',
        ] as $routeName) {
            $this->actingAs($admin)->get(route($routeName))->assertOk();
        }
    }

    public function test_admin_can_create_course_catalog_product_and_pipeline(): void
    {
        $this->seed(ProductionBootstrapSeeder::class);
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('admin.courses.store'), [
            'title' => 'Kursus Dasar STIFIN',
            'slug' => 'kursus-dasar-stifin',
            'summary' => 'Ringkasan kursus',
            'difficulty_level' => 'beginner',
            'estimated_minutes' => 90,
            'is_free' => true,
            'is_published' => true,
        ])->assertRedirect(route('admin.courses.index'));

        $this->assertDatabaseHas('courses', [
            'slug' => 'kursus-dasar-stifin',
            'is_published' => true,
            'author_user_id' => $admin->id,
        ]);

        $this->actingAs($admin)->post(route('admin.products-catalog.store'), [
            'name' => 'Layanan Konsultasi',
            'slug' => 'layanan-konsultasi',
            'type' => 'service',
            'price' => 250000,
            'status' => 'active',
            'visibility' => 'public',
            'is_published' => true,
            'is_catalog_visible' => true,
        ])->assertRedirect(route('admin.products-catalog.index'));

        $this->assertDatabaseHas('products', [
            'slug' => 'layanan-konsultasi',
            'price' => 250000,
            'status' => 'active',
        ]);

        $this->actingAs($admin)->post(route('admin.pipelines.store'), [
            'name' => 'Penjualan Cabang',
            'description' => 'Pipeline utama',
            'is_default' => true,
        ])->assertRedirect(route('admin.pipelines.index'));

        $this->assertDatabaseHas('pipelines', [
            'name' => 'Penjualan Cabang',
            'is_default' => true,
            'owner_user_id' => $admin->id,
        ]);
    }

    public function test_admin_can_create_stifin_result_campaign_and_encrypted_integration(): void
    {
        $this->seed(ProductionBootstrapSeeder::class);
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('admin.stifin-results.store'), [
            'name' => 'Hasil Tes Sofyan',
            'member_user_id' => $admin->id,
            'result_type' => 'pro_stifin',
            'main_result_json' => '{"machine":"Sensing"}',
            'summary_text' => 'Ringkasan hasil.',
            'test_taken_date' => '2026-09-07',
            'is_sensitive_locked' => true,
        ])->assertRedirect(route('admin.stifin-results.index'));

        $this->assertDatabaseHas('stifin_results', [
            'name' => 'Hasil Tes Sofyan',
            'member_user_id' => $admin->id,
            'uploaded_by_user_id' => $admin->id,
        ]);

        $this->actingAs($admin)->post(route('admin.campaigns.store'), [
            'name' => 'Informasi Cabang',
            'type' => 'broadcast',
            'channel' => 'whatsapp',
            'status' => 'draft',
            'audience_type' => 'list',
        ])->assertRedirect(route('admin.campaigns.index'));

        $this->assertDatabaseHas('campaigns', [
            'name' => 'Informasi Cabang',
            'type' => 'broadcast',
            'schedule_send_at' => null,
            'launched_by_user_id' => $admin->id,
        ]);

        $this->actingAs($admin)->post(route('admin.integrations.store'), [
            'display_name' => 'STIFIN Pusat',
            'provider_name' => 'APRO STIFIN',
            'provider_category' => 'stifin_api',
            'provider_type' => 'apro',
            'credentials_json' => '{"token":"secret-token"}',
            'config_json' => '{"base_url":"https://example.test"}',
            'status' => 'configured',
            'is_active' => true,
            'is_primary' => true,
        ])->assertRedirect(route('admin.integrations.index'));

        $this->assertDatabaseHas('integration_connections', [
            'display_name' => 'STIFIN Pusat',
            'provider_category' => 'stifin_api',
            'provider_type' => 'apro',
            'owned_by_user_id' => $admin->id,
        ]);

        $this->assertSame('secret-token', \App\Models\IntegrationConnection::query()->firstOrFail()->getCredentials()['token']);
    }

    public function test_admin_point_adjustment_tracks_direction_and_running_balance(): void
    {
        $this->seed(ProductionBootstrapSeeder::class);
        $admin = $this->admin();
        $member = User::factory()->create();

        $this->actingAs($admin)->post(route('admin.points-ledger.store'), [
            'user_id' => $member->id,
            'direction' => 'credit',
            'amount_points' => 75,
            'notes' => 'Bonus manual',
        ])->assertRedirect();

        $this->actingAs($admin)->post(route('admin.points-ledger.store'), [
            'user_id' => $member->id,
            'direction' => 'debit',
            'amount_points' => 25,
            'notes' => 'Koreksi manual',
        ])->assertRedirect();

        $this->assertDatabaseHas('point_ledger_entries', [
            'user_id' => $member->id,
            'entry_type' => 'adjustment',
            'direction' => 'in',
            'amount_points' => 75,
            'balance_after_points' => 75,
            'reason_text' => 'Bonus manual',
            'performed_by_user_id' => $admin->id,
        ]);
        $this->assertDatabaseHas('point_ledger_entries', [
            'user_id' => $member->id,
            'entry_type' => 'adjustment',
            'direction' => 'out',
            'amount_points' => 25,
            'balance_after_points' => 50,
            'reason_text' => 'Koreksi manual',
            'performed_by_user_id' => $admin->id,
        ]);
    }

    public function test_admin_extended_module_detail_and_edit_pages_render(): void
    {
        $this->seed(ProductionBootstrapSeeder::class);
        $admin = $this->admin();

        $course = \App\Models\Course::query()->create([
            'title' => 'Kursus Tes',
            'slug' => 'kursus-tes',
            'difficulty_level' => 'beginner',
            'author_user_id' => $admin->id,
        ]);
        $product = \App\Models\Product::query()->create([
            'name' => 'Produk Tes',
            'slug' => 'produk-tes',
            'type' => 'service',
            'status' => 'draft',
            'visibility' => 'public',
            'price' => 1000,
        ]);
        $result = \App\Models\StifinResult::query()->create([
            'name' => 'Hasil Tes',
            'member_user_id' => $admin->id,
            'uploaded_by_user_id' => $admin->id,
            'result_type' => 'pro_stifin',
        ]);
        $campaign = \App\Models\Campaign::query()->create([
            'name' => 'Campaign Tes',
            'type' => 'broadcast',
            'channel' => 'whatsapp',
            'status' => 'draft',
            'launched_by_user_id' => $admin->id,
        ]);
        $pipeline = \App\Models\Pipeline::query()->create([
            'name' => 'Pipeline Tes',
            'owner_user_id' => $admin->id,
        ]);
        $integration = \App\Models\IntegrationConnection::query()->create([
            'display_name' => 'Integrasi Tes',
            'provider_name' => 'Provider Tes',
            'provider_category' => 'stifin_api',
            'provider_type' => 'test',
            'encrypted_credentials' => ['token' => 'secret'],
            'status' => 'configured',
        ]);

        foreach ([
            route('admin.courses.show', $course),
            route('admin.courses.edit', $course),
            route('admin.products-catalog.show', $product),
            route('admin.products-catalog.edit', $product),
            route('admin.stifin-results.show', $result),
            route('admin.stifin-results.edit', $result),
            route('admin.campaigns.show', $campaign),
            route('admin.campaigns.edit', $campaign),
            route('admin.pipelines.show', $pipeline),
            route('admin.pipelines.edit', $pipeline),
            route('admin.integrations.edit', $integration),
        ] as $url) {
            $this->actingAs($admin)->get($url)->assertOk();
        }
    }

    public function test_completed_admin_modules_are_visible_without_enabling_member_prototypes(): void
    {
        $this->seed(ProductionBootstrapSeeder::class);
        $admin = $this->admin();
        config()->set('stiflow.admin_extended_modules_enabled', true);
        config()->set('stiflow.prototype_modules_enabled', false);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        foreach ([
            'admin.courses.index',
            'admin.products-catalog.index',
            'admin.stifin-results.index',
            'admin.campaigns.index',
            'admin.pipelines.index',
            'admin.integrations.index',
            'admin.points-ledger.index',
            'admin.staff.permissions',
            'admin.audit.index',
        ] as $routeName) {
            $response->assertSee(route($routeName), false);
        }

        $response->assertDontSee(route('member.kelas.index'), false);
    }

    public function test_pipeline_stage_routes_create_update_and_delete_the_nested_stage(): void
    {
        $this->seed(ProductionBootstrapSeeder::class);
        $admin = $this->admin();
        $pipeline = \App\Models\Pipeline::query()->create([
            'name' => 'Pipeline Operasional',
            'owner_user_id' => $admin->id,
        ]);

        $this->actingAs($admin)->post(route('admin.pipelines.stages.store', $pipeline), [
            'name' => 'Prospek Baru',
            'color_hex' => '#2563eb',
        ])->assertRedirect();

        $stage = \App\Models\PipelineStage::query()->firstOrFail();
        $this->assertSame($pipeline->id, $stage->pipeline_id);
        $this->assertSame('prospek-baru', $stage->slug);
        $this->assertSame(0, $stage->position);

        $this->actingAs($admin)->put(route('admin.pipelines.stages.update', [$pipeline, $stage]), [
            'name' => 'Sudah Dihubungi',
            'color_hex' => '#16a34a',
        ])->assertRedirect();
        $this->assertDatabaseHas('pipeline_stages', ['id' => $stage->id, 'slug' => 'sudah-dihubungi']);

        $this->actingAs($admin)->delete(route('admin.pipelines.stages.destroy', [$pipeline, $stage]))->assertRedirect();
        $this->assertDatabaseMissing('pipeline_stages', ['id' => $stage->id]);
    }

    public function test_production_bootstrap_provides_the_staff_permission_catalog(): void
    {
        $this->seed(ProductionBootstrapSeeder::class);

        $this->assertDatabaseHas('permissions', ['key' => 'users.manage', 'group' => 'admin']);
        $this->assertDatabaseHas('permissions', ['key' => 'courses.manage', 'group' => 'courses']);
        $this->assertDatabaseHas('permissions', ['key' => 'integrations.manage', 'group' => 'integrations']);
        $this->assertGreaterThanOrEqual(9, \App\Models\Permission::query()->count());
    }

    public function test_integration_edit_never_renders_the_plaintext_credential(): void
    {
        $this->seed(ProductionBootstrapSeeder::class);
        $admin = $this->admin();
        $integration = \App\Models\IntegrationConnection::query()->create([
            'display_name' => 'Rahasia',
            'provider_name' => 'Provider',
            'provider_category' => 'stifin_api',
            'provider_type' => 'apro',
            'encrypted_credentials' => ['token' => 'jangan-tampilkan-token-ini'],
            'status' => 'configured',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.integrations.edit', $integration))
            ->assertOk()
            ->assertDontSee('jangan-tampilkan-token-ini');
    }

    public function test_admin_cannot_debit_more_points_than_the_member_balance(): void
    {
        $this->seed(ProductionBootstrapSeeder::class);
        $admin = $this->admin();
        $member = User::factory()->create();

        $this->actingAs($admin)
            ->from(route('admin.points-ledger.index'))
            ->post(route('admin.points-ledger.store'), [
                'user_id' => $member->id,
                'direction' => 'debit',
                'amount_points' => 10,
                'notes' => 'Tidak boleh minus',
            ])
            ->assertRedirect(route('admin.points-ledger.index'))
            ->assertSessionHasErrors('amount_points');

        $this->assertDatabaseCount('point_ledger_entries', 0);
    }

    public function test_message_template_crud_uses_the_real_schema_fields(): void
    {
        $this->seed(ProductionBootstrapSeeder::class);
        $admin = $this->admin();

        $this->actingAs($admin)->get(route('admin.templates.create'))->assertOk();
        $this->actingAs($admin)->post(route('admin.templates.store'), [
            'name' => 'Konfirmasi Pembayaran',
            'channel' => 'whatsapp',
            'template_type' => 'transactional',
            'subject_line' => 'Pembayaran diterima',
            'content_body' => 'Halo {{name}}, pembayaran Anda diterima.',
            'language_code' => 'id',
            'is_active' => true,
        ])->assertRedirect(route('admin.templates.index'));

        $template = \App\Models\MessageTemplate::query()->firstOrFail();
        $this->assertSame($admin->id, $template->created_by_user_id);
        $this->assertSame('Pembayaran diterima', $template->subject_line);

        $this->actingAs($admin)->get(route('admin.templates.show', $template))->assertOk();
        $this->actingAs($admin)->get(route('admin.templates.edit', $template))->assertOk();
    }

    public function test_product_variant_crud_uses_the_real_schema_fields(): void
    {
        $this->seed(ProductionBootstrapSeeder::class);
        $admin = $this->admin();
        $product = \App\Models\Product::query()->firstOrFail();

        $this->actingAs($admin)->get(route('admin.variants.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.variants.create', ['product_id' => $product->id]))->assertOk();
        $this->actingAs($admin)->post(route('admin.variants.store'), [
            'product_id' => $product->id,
            'name' => 'Paket Lima',
            'sku' => 'STIFIN-5',
            'price_override' => 475000,
            'stock_qty' => 20,
            'in_stock' => true,
            'attributes_json' => '{"quantity":5}',
            'sort_order' => 1,
            'is_active' => true,
        ])->assertRedirect(route('admin.variants.index'));

        $variant = \App\Models\ProductVariant::query()->firstOrFail();
        $this->assertSame(475000.0, (float) $variant->price_override);
        $this->assertSame(['quantity' => 5], $variant->attributes_json);
        $this->actingAs($admin)->get(route('admin.variants.show', $variant))->assertOk();
        $this->actingAs($admin)->get(route('admin.variants.edit', $variant))->assertOk();
    }

    public function test_contact_list_and_segment_endpoints_use_their_own_handlers(): void
    {
        $this->seed(ProductionBootstrapSeeder::class);
        $admin = $this->admin();

        $this->actingAs($admin)->get(route('admin.lists.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.segments.index'))->assertOk();

        $this->actingAs($admin)->post(route('admin.lists.store'), [
            'name' => 'Prospek Aktif',
            'description' => 'Prospek yang dapat menerima promosi.',
            'is_marketable_only' => true,
        ])->assertRedirect();

        $this->actingAs($admin)->post(route('admin.segments.store'), [
            'name' => 'Pembeli Voucher',
            'description' => 'Sudah pernah membeli voucher.',
            'filter_rules_json' => '{"has_order":true}',
        ])->assertRedirect();

        $this->assertDatabaseHas('contact_lists', [
            'name' => 'Prospek Aktif',
            'created_by_user_id' => $admin->id,
        ]);
        $segment = \App\Models\Segment::query()->firstOrFail();
        $this->assertSame(['has_order' => true], $segment->filter_rules_json);
        $this->assertSame($admin->id, $segment->created_by_user_id);
    }
}
