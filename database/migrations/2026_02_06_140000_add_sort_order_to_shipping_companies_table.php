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
        Schema::table('shipping_companies', function (Blueprint $table) {
            $table->unsignedInteger('sort_order')->default(0)->after('color');
        });
        // Per-store order: assign sort_order by id within each store_id
        $rows = DB::table('shipping_companies')->orderBy('store_id')->orderBy('id')->get();
        $order = 0;
        $lastStoreId = null;
        foreach ($rows as $row) {
            if ($row->store_id !== $lastStoreId) {
                $order = 0;
                $lastStoreId = $row->store_id;
            }
            $order++;
            DB::table('shipping_companies')->where('id', $row->id)->update(['sort_order' => $order]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shipping_companies', function (Blueprint $table) {
            $table->dropColumn('sort_order');
        });
    }
};
