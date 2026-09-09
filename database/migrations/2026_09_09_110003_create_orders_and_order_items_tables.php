<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique()->index();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone');
            $table->string('shipping_address');
            $table->text('shipping_notes')->nullable();
            $table->string('payment_method')->default('cod'); // 'cod', 'bank_transfer'
            $table->string('payment_status')->default('pending'); // 'pending', 'paid'
            $table->string('status')->default('pending'); // 'pending', 'confirmed', 'shipping', 'completed', 'cancelled'
            $table->integer('subtotal');
            $table->integer('shipping_fee')->default(30000);
            $table->integer('discount_amount')->default(0);
            $table->integer('total_amount');
            $table->string('coupon_code')->nullable();
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('book_id')->nullable()->constrained()->nullOnDelete();
            $table->string('book_title');
            $table->string('book_cover')->nullable();
            $table->integer('price');
            $table->integer('quantity')->default(1);
            $table->integer('total');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
