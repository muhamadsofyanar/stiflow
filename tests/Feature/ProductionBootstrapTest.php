<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Database\Seeders\ProductionBootstrapSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductionBootstrapTest extends TestCase
{
    use RefreshDatabase;

    public function test_production_bootstrap_creates_required_voucher_mvp_data_idempotently(): void
    {
        config()->set('stiflow.branch', [
            'branch_code' => 'TES-CAB-001',
            'brand_name' => 'STIFLow Cabang Tes',
            'contact' => '08123456789',
            'address' => 'Alamat cabang tes',
            'bank_name' => 'BCA',
            'bank_account' => '1234567890',
            'bank_account_name' => 'STIFLow Cabang Tes',
            'locale' => 'id_ID',
            'timezone' => 'Asia/Jakarta',
            'currency' => 'IDR',
        ]);
        config()->set('stiflow.voucher', [
            'name' => 'Voucher STIFIN Satuan',
            'slug' => 'voucher-stifin-satuan',
            'unit_price' => 100000,
            'min_qty' => 1,
            'max_qty' => 100,
            'presets' => [1, 5, 10, 25, 50],
        ]);

        $this->seed(ProductionBootstrapSeeder::class);
        $this->seed(ProductionBootstrapSeeder::class);

        $this->assertDatabaseCount('branch_settings', 1);
        $this->assertDatabaseHas('branch_settings', [
            'branch_code' => 'TES-CAB-001',
            'bank_account' => '1234567890',
        ]);
        $this->assertDatabaseCount('products', 1);
        $this->assertDatabaseHas('products', [
            'slug' => 'voucher-stifin-satuan',
            'status' => 'active',
            'type' => 'voucher',
        ]);
        $this->assertDatabaseCount('voucher_product_configs', 1);
        $this->assertDatabaseHas('voucher_product_configs', [
            'unit_price' => 100000,
            'min_qty' => 1,
            'max_qty' => 100,
        ]);
        $this->assertDatabaseCount('users', 0);
    }

    public function test_voucher_mvp_navigation_exposes_only_working_admin_pages(): void
    {
        config()->set('stiflow.prototype_modules_enabled', false);
        config()->set('stiflow.admin_extended_modules_enabled', false);
        $this->seed(ProductionBootstrapSeeder::class);

        $admin = User::factory()->create([
            'role' => UserRole::Admin,
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk();
        foreach ([
            'admin.dashboard',
            'admin.orders.index',
            'admin.promotors.index',
            'admin.products.index',
            'admin.reconciliation.index',
            'admin.branch-settings.edit',
        ] as $routeName) {
            $response->assertSee(route($routeName), false);
        }

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
            $response->assertDontSee(route($routeName), false);
        }
    }

    public function test_seeded_voucher_mvp_admin_pages_render_successfully(): void
    {
        config()->set('stiflow.prototype_modules_enabled', false);
        $this->seed(ProductionBootstrapSeeder::class);

        $admin = User::factory()->create([
            'role' => UserRole::Admin,
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        foreach ([
            'admin.dashboard',
            'admin.orders.index',
            'admin.promotors.index',
            'admin.products.index',
            'admin.reconciliation.index',
            'admin.branch-settings.edit',
        ] as $routeName) {
            $this->actingAs($admin)->get(route($routeName))->assertOk();
        }
    }
}
