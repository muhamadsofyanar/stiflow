<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                if (! Schema::hasColumn('products', 'slug')) {
                    $table->string('slug')->unique()->nullable();
                }
                if (! Schema::hasColumn('products', 'is_published')) {
                    $table->boolean('is_published')->default(false);
                }
                if (! Schema::hasColumn('products', 'published_at')) {
                    $table->timestamp('published_at')->nullable();
                }
                if (! Schema::hasColumn('products', 'meta_description')) {
                    $table->text('meta_description')->nullable();
                }
                if (! Schema::hasColumn('products', 'featured_image_path')) {
                    $table->string('featured_image_path')->nullable();
                }
                if (! Schema::hasColumn('products', 'is_catalog_visible')) {
                    $table->boolean('is_catalog_visible')->default(true);
                }
                if (! Schema::hasColumn('products', 'sort_order')) {
                    $table->integer('sort_order')->default(0);
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                if (Schema::hasColumn('products', 'sort_order')) {
                    $table->dropColumn('sort_order');
                }
                if (Schema::hasColumn('products', 'is_catalog_visible')) {
                    $table->dropColumn('is_catalog_visible');
                }
                if (Schema::hasColumn('products', 'featured_image_path')) {
                    $table->dropColumn('featured_image_path');
                }
                if (Schema::hasColumn('products', 'meta_description')) {
                    $table->dropColumn('meta_description');
                }
                if (Schema::hasColumn('products', 'published_at')) {
                    $table->dropColumn('published_at');
                }
                if (Schema::hasColumn('products', 'is_published')) {
                    $table->dropColumn('is_published');
                }
            });
        }
    }
};
