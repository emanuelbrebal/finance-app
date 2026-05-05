<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recurring_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('description', 255);
            $table->decimal('amount', 14, 2);
            $table->string('direction', 10); // 'in' or 'out'
            $table->smallInteger('day_of_month'); // 1-31
            $table->date('starts_on');
            $table->date('ends_on')->nullable();
            $table->date('last_generated_on')->nullable();
            $table->boolean('active')->default(true);
            $table->timestampsTz();

            $table->index(['user_id', 'active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recurring_transactions');
    }
};
