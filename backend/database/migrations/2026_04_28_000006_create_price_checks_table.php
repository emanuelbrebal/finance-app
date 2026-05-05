<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('price_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wishlist_item_id')->constrained()->cascadeOnDelete();
            $table->string('source', 40); // 'manual', 'serpapi'
            $table->string('store_name', 120);
            $table->decimal('price', 14, 2);
            $table->string('url', 500);
            $table->timestampTz('found_at');

            $table->index(['wishlist_item_id', 'found_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('price_checks');
    }
};
