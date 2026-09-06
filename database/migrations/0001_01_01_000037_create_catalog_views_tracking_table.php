<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('product_catalog_views')) {
            Schema::create('product_catalog_views', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->nullable()->constrained('products')->cascadeOnDelete();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->text('referer')->nullable();
                $table->timestamp('viewed_at')->useCurrent();
                $table->foreignId('contact_id')->nullable()->constrained('contacts')->nullOnDelete();
                $table->uuid('session_uuid')->nullable();
                $table->index(['product_id', 'viewed_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('product_catalog_views');
    }
};
