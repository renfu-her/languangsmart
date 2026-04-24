<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::statement("ALTER TABLE `orders` MODIFY COLUMN `payment_method` ENUM('現金', '月結', '週結', '日結', '匯款', '刷卡', '行動支付') NULL");
        DB::statement("ALTER TABLE `orders` MODIFY COLUMN `status` ENUM('已預訂', '進行中', '待接送', '已完成', '在合作商', '已結清') DEFAULT '已預訂'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::statement("ALTER TABLE `orders` MODIFY COLUMN `payment_method` ENUM('現金', '月結', '日結', '匯款', '刷卡', '行動支付') NULL");
        DB::statement("ALTER TABLE `orders` MODIFY COLUMN `status` ENUM('已預訂', '進行中', '待接送', '已完成', '在合作商') DEFAULT '已預訂'");
    }
};
