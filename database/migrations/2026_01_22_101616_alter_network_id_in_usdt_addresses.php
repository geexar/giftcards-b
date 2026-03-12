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
        Schema::table('usdt_addresses', function (Blueprint $table) {
            $table->dropColumn('network_identifier');
            $table->foreignId('network_id')->nullable()->after('user_id')->constrained('usdt_networks')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usdt_addresses', function (Blueprint $table) {
            $table->string('network_identifier');
            $table->dropForeign(['network_id']);
            $table->dropColumn(['network_id']);
        });
    }
};
