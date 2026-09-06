<?php

namespace Database\Seeders;

use App\Models\BranchSetting;
use App\Models\Product;
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
        });
    }
}
