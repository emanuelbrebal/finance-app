<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wishlist_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name', 180);
            $table->decimal('target_price', 14, 2);
            $table->decimal('current_price', 14, 2)->nullable();
            $table->string('reference_url', 500)->nullable();
            $table->string('photo_path', 255)->nullable();
            $table->smallInteger('priority')->default(3); // 1-5
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->smallInteger('quarantine_days')->default(30);
            $table->string('status', 20)->default('waiting'); // waiting|ready_to_buy|purchased|abandoned
            $table->foreignId('purchased_transaction_id')->nullable()->constrained('transactions')->nullOnDelete();
            $table->timestampTz('abandoned_at')->nullable();
            $table->timestampTz('last_review_prompt_at')->nullable();
            $table->timestampsTz();

            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'priority']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wishlist_items');
    }
};
