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
        Schema::create('contact_infos', function (Blueprint $table) {
            $table->id();
            $table->string('store_name'); // 店名，例如：蘭光電動機車小琉球店
            $table->string('address')->nullable(); // 地址
            $table->string('phone')->nullable(); // 電話
            $table->string('line_id')->nullable(); // LINE ID
            $table->integer('sort_order')->default(0); // 排序
            $table->boolean('is_active')->default(true); // 是否啟用
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_infos');
    }
};
