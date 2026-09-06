<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('promoter_profiles', function (Blueprint $table) {
            $table->foreignId('sponsor_promoter_profile_id')->nullable()->after('user_id')->constrained('promoter_profiles')->nullOnDelete();
            $table->foreignId('upline_path_root_promoter_profile_id')->nullable()->after('sponsor_promoter_profile_id')->constrained('promoter_profiles')->nullOnDelete();
            $table->unsignedInteger('level_depth')->default(0)->after('upline_path_root_promoter_profile_id');
            $table->string('default_commission_plan_name')->nullable()->after('verification_status');
        });
    }

    public function down(): void
    {
        Schema::table('promoter_profiles', function (Blueprint $table) {
            $table->dropForeign(['sponsor_promoter_profile_id']);
            $table->dropForeign(['upline_path_root_promoter_profile_id']);
            $table->dropColumn([
                'sponsor_promoter_profile_id',
                'upline_path_root_promoter_profile_id',
                'level_depth',
                'default_commission_plan_name',
            ]);
        });
    }
};
