<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('line_id');
            $table->string('phone')->nullable();
            $table->string('scooter_type');
            $table->date('booking_date');
            $table->string('rental_days');
            $table->text('note')->nullable();
            $table->enum('status', ['執行中', '已經回覆', '取消'])->default('執行中');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
