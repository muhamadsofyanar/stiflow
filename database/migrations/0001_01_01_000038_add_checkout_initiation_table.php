<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('checkout_initiations')) {
            Schema::create('checkout_initiations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
                $table->foreignId('variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
                $table->string('session_uuid')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->decimal('proposed_total', 15, 2)->default(0);
                $table->timestamp('initiated_at')->useCurrent();
                $table->boolean('was_completed')->default(false);
                $table->foreignId('resulting_order_id')->nullable()->constrained('orders')->nullOnDelete();
                $table->foreignId('contact_id')->nullable()->constrained('contacts')->nullOnDelete();
                $table->index(['product_id', 'initiated_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('checkout_initiations');
    }
};
