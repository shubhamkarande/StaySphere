<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('host_id')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->text('description');
            $table->string('property_type');
            $table->string('room_type');
            $table->integer('accommodates');
            $table->integer('bedrooms');
            $table->decimal('bathrooms', 3, 1);
            $table->decimal('price_per_night', 8, 2);
            $table->decimal('cleaning_fee', 8, 2)->nullable();
            $table->decimal('service_fee', 8, 2)->nullable();
            $table->string('address');
            $table->string('city');
            $table->string('state');
            $table->string('country');
            $table->string('postal_code');
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->json('amenities')->nullable();
            $table->json('house_rules')->nullable();
            $table->string('cancellation_policy')->default('moderate');
            $table->integer('minimum_nights')->default(1);
            $table->integer('maximum_nights')->default(365);
            $table->boolean('instant_book')->default(false);
            $table->boolean('is_active')->default(true);
            $table->json('images')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['city', 'is_active']);
            $table->index(['price_per_night', 'is_active']);
            $table->index(['accommodates', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('listings');
    }
};