<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('promoter_profiles', function (Blueprint $table) {
            $table->foreignId('verified_by_user_id')
                ->nullable()
                ->after('verified_at')
                ->constrained('users')
                ->nullOnDelete();
            $table->text('verification_notes')->nullable()->after('verified_by_user_id');
        });
    }

    public function down(): void
    {
        Schema::table('promoter_profiles', function (Blueprint $table) {
            $table->dropConstrainedForeignId('verified_by_user_id');
            $table->dropColumn('verification_notes');
        });
    }
};
