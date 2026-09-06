<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('download_activity_snapshots')) {
            Schema::create('download_activity_snapshots', function (Blueprint $table) {
                $table->id();
                $table->date('snapshot_date');
                $table->unsignedInteger('total_downloads')->default(0);
                $table->unsignedInteger('unique_downloaders_count')->default(0);
                $table->unsignedInteger('grants_created_count')->default(0);
                $table->unsignedInteger('grants_revoked_count')->default(0);
                $table->unsignedInteger('grants_expired_count')->default(0);
                $table->unsignedInteger('failed_download_attempts_count')->default(0);
                $table->decimal('bandwidth_mb', 15, 2)->default(0);
                $table->timestamps();
                $table->unique('snapshot_date');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('download_activity_snapshots');
    }
};
