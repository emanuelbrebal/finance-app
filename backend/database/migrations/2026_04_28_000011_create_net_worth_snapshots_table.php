<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('net_worth_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('captured_on'); // last day of the month
            $table->decimal('total_assets', 14, 2);
            $table->jsonb('total_by_account')->default('{}');
            $table->decimal('monthly_income', 14, 2);
            $table->decimal('monthly_expenses', 14, 2);
            $table->decimal('savings_rate', 5, 2); // (income-expenses)/income * 100
            $table->timestampTz('created_at');

            $table->unique(['user_id', 'captured_on']);
            $table->index(['user_id', 'captured_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('net_worth_snapshots');
    }
};
