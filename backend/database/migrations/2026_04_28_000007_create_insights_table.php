<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('insights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type', 40); // 'savings_rate_record', 'category_spike', etc.
            $table->string('severity', 10); // 'positive', 'info', 'warning'
            $table->string('title', 180);
            $table->text('body');
            $table->jsonb('payload')->default('{}');
            $table->string('dedup_key', 120);
            $table->timestampTz('read_at')->nullable();
            $table->timestampTz('dismissed_at')->nullable();
            $table->timestampTz('created_at');

            $table->index(['user_id', 'created_at']);
            $table->unique(['user_id', 'dedup_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('insights');
    }
};
