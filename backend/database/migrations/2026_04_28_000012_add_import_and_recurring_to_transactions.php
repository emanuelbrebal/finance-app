<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->foreignId('import_batch_id')
                ->nullable()
                ->after('dedup_hash')
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('recurring_transaction_id')
                ->nullable()
                ->after('import_batch_id')
                ->constrained()
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\ImportBatch::class);
            $table->dropForeignIdFor(\App\Models\RecurringTransaction::class);
            $table->dropColumn(['import_batch_id', 'recurring_transaction_id']);
        });
    }
};
