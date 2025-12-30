<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 在 MySQL 中修改 enum 欄位需要使用 ALTER TABLE
        DB::statement("ALTER TABLE `orders` MODIFY COLUMN `payment_method` ENUM('現金', '月結', '日結', '匯款', '刷卡', '行動支付') NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 還原為原本的三個選項
        DB::statement("ALTER TABLE `orders` MODIFY COLUMN `payment_method` ENUM('現金', '月結', '日結') NULL");
    }
};

