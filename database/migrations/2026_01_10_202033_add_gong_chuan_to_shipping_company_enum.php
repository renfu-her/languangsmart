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
        // 修改 bookings 表的 shipping_company enum
        DB::statement("ALTER TABLE bookings MODIFY COLUMN shipping_company ENUM('泰富', '藍白', '聯營', '大福', '公船') NULL");
        
        // 修改 orders 表的 shipping_company enum
        DB::statement("ALTER TABLE orders MODIFY COLUMN shipping_company ENUM('泰富', '藍白', '聯營', '大福', '公船') NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 還原 bookings 表的 shipping_company enum
        DB::statement("ALTER TABLE bookings MODIFY COLUMN shipping_company ENUM('泰富', '藍白', '聯營', '大福') NULL");
        
        // 還原 orders 表的 shipping_company enum
        DB::statement("ALTER TABLE orders MODIFY COLUMN shipping_company ENUM('泰富', '藍白', '聯營', '大福') NULL");
    }
};
