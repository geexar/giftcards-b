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
        Schema::table('product_variant_values', function (Blueprint $table) {
            $table->integer('reserved_stock')->default(0)->after('manual_stock');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_variant_values', function (Blueprint $table) {
            $table->dropColumn('reserved_stock');
        });
    }
};
