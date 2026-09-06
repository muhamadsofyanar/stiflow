<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('automation_flows')) {
            Schema::table('automation_flows', function (Blueprint $table) {
                if (! Schema::hasColumn('automation_flows', 'trigger_type')) {
                    $table->enum('trigger_type', [
                        'OrderPaid',
                        'LeadCreated',
                        'ContactStageChanged',
                        'CampaignScheduled',
                    ])->nullable()->after('name');
                }
                if (! Schema::hasColumn('automation_flows', 'trigger_config_json')) {
                    $table->json('trigger_config_json')->nullable()->after('trigger_type');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('automation_flows')) {
            Schema::table('automation_flows', function (Blueprint $table) {
                if (Schema::hasColumn('automation_flows', 'trigger_config_json')) {
                    $table->dropColumn('trigger_config_json');
                }
                if (Schema::hasColumn('automation_flows', 'trigger_type')) {
                    $table->dropColumn('trigger_type');
                }
            });
        }
    }
};
