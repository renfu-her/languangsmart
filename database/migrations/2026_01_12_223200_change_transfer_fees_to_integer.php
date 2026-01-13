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
        // 先將現有的 decimal 值轉換為整數（四捨五入）
        DB::statement('UPDATE partners SET 
            same_day_transfer_fee_white = ROUND(COALESCE(same_day_transfer_fee_white, 0)),
            same_day_transfer_fee_green = ROUND(COALESCE(same_day_transfer_fee_green, 0)),
            same_day_transfer_fee_electric = ROUND(COALESCE(same_day_transfer_fee_electric, 0)),
            same_day_transfer_fee_tricycle = ROUND(COALESCE(same_day_transfer_fee_tricycle, 0)),
            overnight_transfer_fee_white = ROUND(COALESCE(overnight_transfer_fee_white, 0)),
            overnight_transfer_fee_green = ROUND(COALESCE(overnight_transfer_fee_green, 0)),
            overnight_transfer_fee_electric = ROUND(COALESCE(overnight_transfer_fee_electric, 0)),
            overnight_transfer_fee_tricycle = ROUND(COALESCE(overnight_transfer_fee_tricycle, 0))
        ');

        Schema::table('partners', function (Blueprint $table) {
            // 修改當日調車費用欄位為 unsignedInteger
            $table->unsignedInteger('same_day_transfer_fee_white')->nullable()->change();
            $table->unsignedInteger('same_day_transfer_fee_green')->nullable()->change();
            $table->unsignedInteger('same_day_transfer_fee_electric')->nullable()->change();
            $table->unsignedInteger('same_day_transfer_fee_tricycle')->nullable()->change();
            
            // 修改跨日調車費用欄位為 unsignedInteger
            $table->unsignedInteger('overnight_transfer_fee_white')->nullable()->change();
            $table->unsignedInteger('overnight_transfer_fee_green')->nullable()->change();
            $table->unsignedInteger('overnight_transfer_fee_electric')->nullable()->change();
            $table->unsignedInteger('overnight_transfer_fee_tricycle')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('partners', function (Blueprint $table) {
            // 回復為 decimal 類型
            $table->decimal('same_day_transfer_fee_white', 10, 2)->nullable()->change();
            $table->decimal('same_day_transfer_fee_green', 10, 2)->nullable()->change();
            $table->decimal('same_day_transfer_fee_electric', 10, 2)->nullable()->change();
            $table->decimal('same_day_transfer_fee_tricycle', 10, 2)->nullable()->change();
            
            $table->decimal('overnight_transfer_fee_white', 10, 2)->nullable()->change();
            $table->decimal('overnight_transfer_fee_green', 10, 2)->nullable()->change();
            $table->decimal('overnight_transfer_fee_electric', 10, 2)->nullable()->change();
            $table->decimal('overnight_transfer_fee_tricycle', 10, 2)->nullable()->change();
        });
    }
};
