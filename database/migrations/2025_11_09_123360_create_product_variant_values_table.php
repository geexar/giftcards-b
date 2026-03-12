<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_variant_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_variant_id')->constrained('product_variants')->cascadeOnDelete();
            $table->text('description_en')->nullable();
            $table->text('description_ar')->nullable();
            $table->boolean('is_visible')->default(true);
            $table->string('value');
            $table->decimal('base_price', 10, 2);
            $table->boolean('has_discount')->default(false);
            $table->enum('discount_type', ['fixed', 'percentage'])->nullable();
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('final_price', 10, 2);
            $table->string('delivery_type');
            $table->boolean('marked_as_out_of_stock')->default(false);
            $table->integer('quantity')->default(0);
            $table->integer('informational_quantity')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variant_values');
    }
};
