<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('time_slots', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            $table->boolean('is_available')->default(true);
            $table->integer('max_bookings')->default(3);
            $table->timestamps();

            $table->index(['date', 'is_available']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('time_slots');
    }
};
