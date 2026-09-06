<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'meta_json')) {
                $table->json('meta_json')->nullable()->after('description');
            }
            if (!Schema::hasColumn('products', 'package_code')) {
                $table->string('package_code', 50)->nullable()->index()->after('meta_json');
            }
            if (!Schema::hasColumn('products', 'include_scanner')) {
                $table->boolean('include_scanner')->default(false)->after('package_code');
            }
            if (!Schema::hasColumn('products', 'include_wsl_license_access')) {
                $table->boolean('include_wsl_license_access')->default(false)->after('include_scanner');
            }
            if (!Schema::hasColumn('products', 'include_id_card_license')) {
                $table->boolean('include_id_card_license')->default(false)->after('include_wsl_license_access');
            }
            if (!Schema::hasColumn('products', 'stifin_test_type_code')) {
                $table->string('stifin_test_type_code', 50)->nullable()->index()->after('include_id_card_license');
            }
            if (!Schema::hasColumn('products', 'wsl_level')) {
                $table->unsignedTinyInteger('wsl_level')->nullable()->after('stifin_test_type_code');
            }
            if (!Schema::hasColumn('products', 'landing_page_slug')) {
                $table->string('landing_page_slug')->nullable()->unique()->after('wsl_level');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $cols = [];
            foreach ([
                'meta_json','package_code','include_scanner','include_wsl_license_access',
                'include_id_card_license','stifin_test_type_code','wsl_level','landing_page_slug'
            ] as $c) {
                if (Schema::hasColumn('products', $c)) {
                    $cols[] = $c;
                }
            }
            if (count($cols) > 0) {
                $table->dropColumn($cols);
            }
        });
    }
};
