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
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        // 添加「已轉訂單」狀態到 bookings 表的 status enum
        DB::statement("ALTER TABLE `bookings` MODIFY COLUMN `status` ENUM('預約中', '執行中', '已經回覆', '取消', '已轉訂單') DEFAULT '預約中'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        // 移除「已轉訂單」狀態
        DB::statement("ALTER TABLE `bookings` MODIFY COLUMN `status` ENUM('預約中', '執行中', '已經回覆', '取消') DEFAULT '預約中'");
    }
};
