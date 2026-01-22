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
        Schema::table('users', function (Blueprint $table) {
            // 增加 store_id 欄位
            if (!Schema::hasColumn('users', 'store_id')) {
                $table->unsignedBigInteger('store_id')->nullable()->after('status');
                $table->foreign('store_id')->references('id')->on('stores')->onDelete('set null');
            }
            
            // 增加授權欄位
            if (!Schema::hasColumn('users', 'can_manage_stores')) {
                $table->boolean('can_manage_stores')->default(false)->after('store_id');
            }
            
            if (!Schema::hasColumn('users', 'can_manage_content')) {
                $table->boolean('can_manage_content')->default(false)->after('can_manage_stores');
            }
        });

        // 修改 role 欄位：從 ['admin', 'member'] 改為 ['super_admin', 'admin']
        // 先修改 enum 類型，允許新的值
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'member', 'super_admin') NOT NULL DEFAULT 'admin'");
        
        // 然後更新資料：'admin' -> 'super_admin'，'member' -> 'admin'
        DB::statement("UPDATE users SET role = 'super_admin' WHERE role = 'admin'");
        DB::statement("UPDATE users SET role = 'admin' WHERE role = 'member'");
        
        // 最後移除舊的 'member' 選項，只保留 'super_admin' 和 'admin'
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('super_admin', 'admin') NOT NULL DEFAULT 'admin'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // 移除外鍵和欄位
            if (Schema::hasColumn('users', 'store_id')) {
                $table->dropForeign(['store_id']);
                $table->dropColumn('store_id');
            }
            
            if (Schema::hasColumn('users', 'can_manage_stores')) {
                $table->dropColumn('can_manage_stores');
            }
            
            if (Schema::hasColumn('users', 'can_manage_content')) {
                $table->dropColumn('can_manage_content');
            }
        });

        // 還原 role 欄位
        // 先修改 enum 類型，允許舊的值
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('super_admin', 'admin', 'member') NOT NULL DEFAULT 'admin'");
        
        // 然後更新資料：'super_admin' -> 'admin'，'admin' -> 'member'
        DB::statement("UPDATE users SET role = 'admin' WHERE role = 'super_admin'");
        DB::statement("UPDATE users SET role = 'member' WHERE role = 'admin'");
        
        // 最後移除 'super_admin' 選項，只保留 'admin' 和 'member'
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'member') NOT NULL DEFAULT 'member'");
    }
};
