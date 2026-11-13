<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // payOS bắt buộc orderCode là số nguyên dương duy nhất
            $table->unsignedBigInteger('payos_order_code')->nullable()->unique()->after('order_number');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique(['payos_order_code']);
            $table->dropColumn('payos_order_code');
        });
    }
};
