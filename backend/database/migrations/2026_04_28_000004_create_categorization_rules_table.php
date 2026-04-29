<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categorization_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('match_type', 20); // 'contains', 'starts_with', 'regex', 'exact'
            $table->string('pattern', 255);
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->smallInteger('priority')->default(0);
            $table->boolean('auto_learned')->default(false);
            $table->integer('hits')->default(0);
            $table->timestampsTz();

            $table->index(['user_id', 'priority']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categorization_rules');
    }
};
