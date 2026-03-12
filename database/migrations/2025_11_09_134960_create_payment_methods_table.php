<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->json('config')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('active_for_checkout')->default(false);
            $table->boolean('active_for_top_up')->default(false);
            $table->enum('active_mode', ['sandbox', 'live'])->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
