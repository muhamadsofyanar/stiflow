<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreign('coupon_id')->references('id')->on('coupons')->nullOnDelete();
        });
        Schema::table('courses', function (Blueprint $table) {
            $table->foreign('cover_image_id')->references('id')->on('digital_assets')->nullOnDelete();
        });
        Schema::table('lessons', function (Blueprint $table) {
            $table->foreign('digital_asset_id')->references('id')->on('digital_assets')->nullOnDelete();
        });
        Schema::table('campaigns', function (Blueprint $table) {
            $table->foreign('sender_integration_connection_id')->references('id')->on('integration_connections')->nullOnDelete();
        });
        Schema::table('message_deliveries', function (Blueprint $table) {
            $table->foreign('integration_connection_id')->references('id')->on('integration_connections')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('message_deliveries', function (Blueprint $table) {
            $table->dropForeign(['integration_connection_id']);
        });
        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropForeign(['sender_integration_connection_id']);
        });
        Schema::table('lessons', function (Blueprint $table) {
            $table->dropForeign(['digital_asset_id']);
        });
        Schema::table('courses', function (Blueprint $table) {
            $table->dropForeign(['cover_image_id']);
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['coupon_id']);
        });
    }
};
