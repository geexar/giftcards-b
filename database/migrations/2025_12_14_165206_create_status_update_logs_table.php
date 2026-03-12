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
        Schema::create('status_update_logs', function (Blueprint $table) {
            $table->id();
            $table->nullableMorphs('actor');
            $table->morphs('model');
            $table->string('old_status')->nullable();
            $table->string('new_status');
            $table->timestamp('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('status_update_logs');
    }
};
