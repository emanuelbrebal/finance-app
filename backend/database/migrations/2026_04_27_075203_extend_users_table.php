<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('target_net_worth', 14, 2)->nullable()->after('password');
            $table->date('target_date')->nullable()->after('target_net_worth');
            $table->decimal('estimated_monthly_income', 14, 2)->nullable()->after('target_date');
            $table->string('timezone', 64)->default('America/Sao_Paulo')->after('estimated_monthly_income');
            $table->string('journey_level', 40)->nullable()->after('timezone');
            $table->jsonb('preferences')->default('{}')->after('journey_level');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'target_net_worth',
                'target_date',
                'estimated_monthly_income',
                'timezone',
                'journey_level',
                'preferences',
            ]);
        });
    }
};
