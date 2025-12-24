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
        Schema::create('scooters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partner_id')->constrained('partners')->onDelete('cascade');
            $table->string('plate_number')->unique();
            $table->string('model');
            $table->enum('type', ['白牌', '綠牌', '電輔車']);
            $table->string('color')->nullable();
            $table->enum('status', ['待出租', '出租中', '保養中'])->default('待出租');
            $table->string('photo_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scooters');
    }
};

