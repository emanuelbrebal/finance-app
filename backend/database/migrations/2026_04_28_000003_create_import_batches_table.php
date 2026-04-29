<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('import_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->string('importer', 40); // 'ofx', 'nubank_csv', 'nubank_card_csv', 'generic_csv'
            $table->string('original_filename', 255);
            $table->char('file_hash', 64);
            $table->integer('rows_total')->default(0);
            $table->integer('rows_imported')->default(0);
            $table->integer('rows_duplicated')->default(0);
            $table->string('status', 20)->default('pending'); // pending|preview_ready|completed|failed|reverted
            $table->jsonb('preview_payload')->nullable();
            $table->text('error_message')->nullable();
            $table->timestampsTz();

            $table->unique(['user_id', 'file_hash']);
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_batches');
    }
};
