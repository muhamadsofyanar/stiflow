<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('integration_connections')) {
            Schema::table('integration_connections', function (Blueprint $table) {
                if (! Schema::hasColumn('integration_connections', 'provider_sub_type')) {
                    $table->string('provider_sub_type')->nullable()->after('provider_type');
                }
                if (! Schema::hasColumn('integration_connections', 'webhook_signing_secret')) {
                    $table->text('webhook_signing_secret')->nullable()->after('config_json');
                }
                if (! Schema::hasColumn('integration_connections', 'auto_followup_enabled')) {
                    $table->boolean('auto_followup_enabled')->default(false)->after('is_primary');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('integration_connections')) {
            Schema::table('integration_connections', function (Blueprint $table) {
                if (Schema::hasColumn('integration_connections', 'auto_followup_enabled')) {
                    $table->dropColumn('auto_followup_enabled');
                }
                if (Schema::hasColumn('integration_connections', 'webhook_signing_secret')) {
                    $table->dropColumn('webhook_signing_secret');
                }
                if (Schema::hasColumn('integration_connections', 'provider_sub_type')) {
                    $table->dropColumn('provider_sub_type');
                }
            });
        }
    }
};
