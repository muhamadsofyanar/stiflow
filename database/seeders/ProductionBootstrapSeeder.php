<?php

namespace Database\Seeders;

use App\Models\BranchSetting;
use App\Models\Product;
use App\Models\Permission;
use App\Models\VoucherProductConfig;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductionBootstrapSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            if (! BranchSetting::query()->exists()) {
                BranchSetting::query()->create(config('stiflow.branch'));
            }

            $voucher = config('stiflow.voucher');

            $product = Product::query()->firstOrCreate(
                ['slug' => $voucher['slug']],
                [
                    'type' => 'voucher',
                    'name' => $voucher['name'],
                    'status' => 'active',
                    'visibility' => 'login_only',
                    'price' => $voucher['unit_price'],
                    'commission_eligible' => false,
                    'description' => 'Voucher STIFIN per unit untuk promotor cabang.',
                ],
            );

            VoucherProductConfig::query()->firstOrCreate(
                ['product_id' => $product->id],
                [
                    'unit_price' => $voucher['unit_price'],
                    'min_qty' => $voucher['min_qty'],
                    'max_qty' => $voucher['max_qty'],
                    'presets_json' => $voucher['presets'],
                    'free_units_rule_json' => null,
                ],
            );

            foreach ($this->permissions() as $permission) {
                Permission::query()->updateOrCreate(
                    ['key' => $permission['key']],
                    $permission,
                );
            }
        });
    }

    private function permissions(): array
    {
        return [
            ['key' => 'users.manage', 'name' => 'Kelola Pengguna', 'group' => 'admin', 'description' => 'Mengelola pengguna dan staff'],
            ['key' => 'promoters.view', 'name' => 'Lihat Promotor', 'group' => 'promotor', 'description' => 'Melihat daftar promotor'],
            ['key' => 'promoters.manage', 'name' => 'Kelola Promotor', 'group' => 'promotor', 'description' => 'Mengelola promotor'],
            ['key' => 'promoters.verify', 'name' => 'Verifikasi Promotor', 'group' => 'promotor', 'description' => 'Memverifikasi promotor'],
            ['key' => 'contacts.view_all', 'name' => 'Lihat Kontak', 'group' => 'crm', 'description' => 'Melihat seluruh kontak'],
            ['key' => 'contacts.manage_all', 'name' => 'Kelola Kontak', 'group' => 'crm', 'description' => 'Mengelola seluruh kontak'],
            ['key' => 'orders.view', 'name' => 'Lihat Pesanan', 'group' => 'orders', 'description' => 'Melihat pesanan'],
            ['key' => 'orders.manage', 'name' => 'Kelola Pesanan', 'group' => 'orders', 'description' => 'Mengelola pesanan'],
            ['key' => 'payments.verify', 'name' => 'Verifikasi Pembayaran', 'group' => 'payments', 'description' => 'Memverifikasi pembayaran'],
            ['key' => 'vouchers.view', 'name' => 'Lihat Voucher', 'group' => 'vouchers', 'description' => 'Melihat voucher'],
            ['key' => 'vouchers.review', 'name' => 'Review Voucher', 'group' => 'vouchers', 'description' => 'Melakukan review voucher'],
            ['key' => 'products.manage', 'name' => 'Kelola Produk', 'group' => 'catalog', 'description' => 'Mengelola katalog produk'],
            ['key' => 'courses.manage', 'name' => 'Kelola Kursus', 'group' => 'courses', 'description' => 'Mengelola kursus LMS'],
            ['key' => 'payouts.approve', 'name' => 'Setujui Payout', 'group' => 'payouts', 'description' => 'Menyetujui payout'],
            ['key' => 'campaigns.manage', 'name' => 'Kelola Campaign', 'group' => 'campaigns', 'description' => 'Mengelola campaign'],
            ['key' => 'campaigns.send', 'name' => 'Kirim Campaign', 'group' => 'campaigns', 'description' => 'Menjadwalkan campaign'],
            ['key' => 'integrations.manage', 'name' => 'Kelola Integrasi', 'group' => 'integrations', 'description' => 'Mengelola integrasi'],
            ['key' => 'settings.manage', 'name' => 'Kelola Pengaturan', 'group' => 'settings', 'description' => 'Mengelola pengaturan'],
            ['key' => 'audit.view', 'name' => 'Lihat Audit', 'group' => 'audit', 'description' => 'Melihat audit log'],
        ];
    }
}
