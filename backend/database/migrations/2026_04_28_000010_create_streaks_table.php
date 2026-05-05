<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('streaks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('kind', 30); // 'weekly_logging', 'positive_months'
            $table->integer('current_count')->default(0);
            $table->integer('best_count')->default(0);
            $table->date('current_started_on')->nullable();
            $table->date('last_extended_on')->nullable();
            $table->timestampsTz();

            $table->unique(['user_id', 'kind']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('streaks');
    }
};
