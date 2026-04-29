<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('account_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name', 100);
            $table->decimal('target_amount', 14, 2);
            $table->decimal('current_amount', 14, 2)->default(0);
            $table->date('target_date')->nullable();
            $table->boolean('is_emergency_fund')->default(false);
            $table->timestampTz('achieved_at')->nullable();
            $table->timestampsTz();

            $table->index(['user_id', 'achieved_at']);
            // Only one emergency fund per user
            $table->unique(['user_id', 'is_emergency_fund']); // enforced at app level for partial unique
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('goals');
    }
};
