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
        // orders: enum -> string
        DB::statement("ALTER TABLE orders MODIFY COLUMN shipping_company VARCHAR(100) NULL");
        // bookings: enum -> string
        DB::statement("ALTER TABLE bookings MODIFY COLUMN shipping_company VARCHAR(100) NULL");
        // partners: enum -> string
        DB::statement("ALTER TABLE partners MODIFY COLUMN default_shipping_company VARCHAR(100) NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE orders MODIFY COLUMN shipping_company ENUM('泰富', '藍白', '聯營', '大福', '公船') NULL");
        DB::statement("ALTER TABLE bookings MODIFY COLUMN shipping_company ENUM('泰富', '藍白', '聯營', '大福', '公船') NULL");
        DB::statement("ALTER TABLE partners MODIFY COLUMN default_shipping_company ENUM('泰富', '藍白', '聯營', '大福', '公船') NULL");
    }
};
