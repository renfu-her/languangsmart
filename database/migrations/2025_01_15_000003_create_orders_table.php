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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('partner_id')->nullable()->constrained('partners')->onDelete('set null');
            $table->string('tenant');
            $table->date('appointment_date');
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->dateTime('expected_return_time')->nullable();
            $table->string('phone')->nullable();
            $table->enum('shipping_company', ['泰富', '藍白', '聯營', '大福'])->nullable();
            $table->dateTime('ship_arrival_time')->nullable();
            $table->dateTime('ship_return_time')->nullable();
            $table->enum('payment_method', ['現金', '月結', '日結'])->nullable();
            $table->decimal('payment_amount', 10, 2);
            $table->enum('status', ['進行中', '已完成', '已取消', '預約中'])->default('預約中');
            $table->text('remark')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};

