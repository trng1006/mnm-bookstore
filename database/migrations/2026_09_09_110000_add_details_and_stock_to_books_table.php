<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->text('description')->nullable()->after('cover_image');
            $table->integer('stock')->default(50)->after('price_old');
            $table->string('publisher')->nullable()->after('description');
            $table->integer('pages')->nullable()->after('publisher');
            $table->integer('publication_year')->nullable()->after('pages');
        });
    }

    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropColumn(['description', 'stock', 'publisher', 'pages', 'publication_year']);
        });
    }
};
