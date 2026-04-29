<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('milestones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type', 60); // 'net_worth_10k', 'first_emergency_month', etc.
            $table->string('tier', 10); // 'small', 'medium', 'large', 'epic'
            $table->string('title', 180);
            $table->text('body');
            $table->jsonb('payload')->default('{}');
            $table->string('dedup_key', 120);
            $table->timestampTz('achieved_at');
            $table->timestampTz('celebrated_at')->nullable();
            $table->timestampTz('created_at');

            $table->index(['user_id', 'achieved_at']);
            $table->unique(['user_id', 'dedup_key']);
            $table->index(['user_id', 'celebrated_at']); // for pending celebrations query
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('milestones');
    }
};
