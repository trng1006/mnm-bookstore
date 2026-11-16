<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_ai_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action_type', 20); // create, update, delete
            $table->string('target_type', 50); // book, category, author, coupon, review, order
            $table->unsignedBigInteger('target_id')->nullable();
            $table->string('summary', 500);
            $table->json('before_data')->nullable();
            $table->json('after_data')->nullable();
            $table->string('status', 20)->default('executed'); // executed, undone
            $table->foreignId('undone_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('undone_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_ai_logs');
    }
};
