<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->onDelete('cascade');
            $table->foreignId('listing_id')->constrained()->onDelete('cascade');
            $table->foreignId('reviewer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('reviewee_id')->constrained('users')->onDelete('cascade');
            $table->integer('rating');
            $table->text('comment');
            $table->integer('cleanliness_rating');
            $table->integer('communication_rating');
            $table->integer('checkin_rating');
            $table->integer('accuracy_rating');
            $table->integer('location_rating');
            $table->integer('value_rating');
            $table->timestamps();

            $table->unique('booking_id');
            $table->index(['listing_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};