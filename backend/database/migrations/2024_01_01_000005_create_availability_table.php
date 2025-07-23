<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('availability', function (Blueprint $table) {
            $table->id();
            $table->foreignId('listing_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->boolean('is_available')->default(true);
            $table->decimal('price_override', 8, 2)->nullable();
            $table->integer('minimum_nights_override')->nullable();
            $table->timestamps();

            $table->unique(['listing_id', 'date']);
            $table->index(['listing_id', 'date', 'is_available']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('availability');
    }
};