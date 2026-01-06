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
        Schema::table('bookings', function (Blueprint $table) {
            $table->date('end_date')->nullable()->after('booking_date');
            $table->enum('shipping_company', ['泰富', '藍白', '聯營', '大福'])->nullable()->after('phone');
            $table->dateTime('ship_arrival_time')->nullable()->after('shipping_company');
            $table->integer('adults')->nullable()->after('ship_arrival_time');
            $table->integer('children')->nullable()->after('adults');
            $table->json('scooters')->nullable()->after('children'); // 儲存租車類型/數量陣列
            $table->enum('status', ['預約中', '執行中', '已經回覆', '取消'])->default('預約中')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['end_date', 'shipping_company', 'ship_arrival_time', 'adults', 'children', 'scooters']);
            $table->enum('status', ['執行中', '已經回覆', '取消'])->default('執行中')->change();
        });
    }
};
