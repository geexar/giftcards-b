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
        Schema::create('codes', function (Blueprint $table) {
            $table->id();
            $table->morphs('codable');
            $table->string('code');
            $table->string('pin_code')->nullable();
            $table->string('info_1')->nullable();
            $table->string('info_2')->nullable();
            $table->dateTime('expiry_date')->nullable();
            $table->boolean('is_used')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('codes');
    }
};
