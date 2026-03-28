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
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pet_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->foreignId('time_slot_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('pending'); // pending, confirmed, in_progress, completed, cancelled
            $table->string('payment_method')->default('cash'); // online, cash
            $table->string('payment_status')->default('unpaid'); // unpaid, paid, refunded
            $table->decimal('total_price', 8, 2);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
