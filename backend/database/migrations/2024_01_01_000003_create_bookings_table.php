<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('listing_id')->constrained()->onDelete('cascade');
            $table->foreignId('guest_id')->constrained('users')->onDelete('cascade');
            $table->date('check_in');
            $table->date('check_out');
            $table->integer('guests');
            $table->decimal('total_price', 10, 2);
            $table->decimal('cleaning_fee', 8, 2)->nullable();
            $table->decimal('service_fee', 8, 2)->nullable();
            $table->enum('status', ['pending', 'confirmed', 'cancelled', 'completed'])->default('pending');
            $table->string('stripe_payment_intent_id')->nullable();
            $table->text('special_requests')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->timestamps();

            $table->index(['listing_id', 'check_in', 'check_out']);
            $table->index(['guest_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};