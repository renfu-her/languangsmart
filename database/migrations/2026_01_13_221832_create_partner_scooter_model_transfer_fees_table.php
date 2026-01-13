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
        Schema::create('partner_scooter_model_transfer_fees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partner_id')->constrained('partners')->onDelete('cascade');
            $table->foreignId('scooter_model_id')->constrained('scooter_models')->onDelete('cascade');
            $table->unsignedInteger('same_day_transfer_fee')->nullable()->comment('當日調車費用');
            $table->unsignedInteger('overnight_transfer_fee')->nullable()->comment('跨日調車費用');
            $table->timestamps();
            
            // 確保每個合作商對每個機車型號只有一筆記錄
            $table->unique(['partner_id', 'scooter_model_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('partner_scooter_model_transfer_fees');
    }
};
