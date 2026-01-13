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
        if (!Schema::hasTable('scooter_models')) {
            Schema::create('scooter_models', function (Blueprint $table) {
                $table->id();
                $table->string('name')->comment('機車型號名稱，如 ES-1000, ES-2000, EB-500, ES-3000');
                $table->enum('type', ['白牌', '綠牌', '電輔車', '三輪車'])->comment('車型類型');
                $table->string('image_path')->nullable()->comment('圖片路徑');
                $table->string('color')->nullable()->comment('顏色（hex 格式，如 #7DD3FC）');
                $table->timestamps();
                
                // 確保型號名稱唯一
                $table->unique('name');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scooter_models');
    }
};
