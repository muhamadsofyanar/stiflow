<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('social_proof_settings')) {
            Schema::create('social_proof_settings', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->unique()->constrained('products')->cascadeOnDelete();
                $table->boolean('show_recent_purchase')->default(true);
                $table->unsignedInteger('time_window_hours')->default(48);
                $table->unsignedInteger('display_limit')->default(10);
                $table->boolean('anonymize_name')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('event_tracking_pixels')) {
            Schema::create('event_tracking_pixels', function (Blueprint $table) {
                $table->id();
                $table->enum('provider', ['MetaPixel', 'GoogleAnalytics4', 'TikTokPixel', 'LinkedInInsight']);
                $table->string('pixel_id');
                $table->boolean('is_active')->default(true);
                $table->text('script_head')->nullable();
                $table->text('script_body')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('event_tracking_pixels');
        Schema::dropIfExists('social_proof_settings');
    }
};
