<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->date('occurred_on');
            $table->string('description', 255);
            $table->decimal('amount', 14, 2); // always positive
            $table->string('direction', 10); // 'in' or 'out'
            $table->text('notes')->nullable();
            $table->jsonb('tags')->default('[]');
            $table->boolean('out_of_scope')->default(false);
            $table->char('dedup_hash', 64);
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->index(['user_id', 'occurred_on']);
            $table->index(['user_id', 'category_id', 'occurred_on']);
            $table->index(['account_id', 'occurred_on']);
            $table->unique(['user_id', 'dedup_hash']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
