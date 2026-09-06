<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['voucher', 'course', 'digital', 'service', 'membership'])->default('voucher');
            $table->string('name');
            $table->string('slug')->unique();
            $table->enum('status', ['active', 'inactive', 'draft'])->default('draft');
            $table->enum('visibility', ['public', 'login_only', 'segment_only', 'hidden_link'])->default('public');
            $table->decimal('price', 12, 2)->unsigned()->default(0);
            $table->boolean('commission_eligible')->default(true);
            $table->json('points_policy_json')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
