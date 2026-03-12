<?php

use App\Enums\ProductStatus;
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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('source');
            $table->string('external_id')->nullable();
            $table->string('sku')->unique();
            $table->json('name');
            $table->json('short_description')->nullable();
            $table->json('description')->nullable();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status')->default(ProductStatus::ACTIVE->value);
            $table->boolean('is_global')->default(true);
            $table->boolean('has_custom_markup_fees')->default(false);
            $table->enum('custom_markup_fees_type', ['fixed', 'percentage'])->nullable();
            $table->decimal('custom_markup_fees', 10, 2)->default(0);
            $table->boolean('has_variants')->default(false);
            $table->decimal('base_price', 10, 2);
            $table->boolean('has_discount')->default(false);
            $table->enum('discount_type', ['fixed', 'percentage'])->nullable();
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('final_price', 10, 2);
            $table->enum('delivery_type', ['instant', 'requires_confirmation']);
            $table->boolean('marked_as_out_of_stock')->default(false);
            $table->integer('quantity')->default(0);
            $table->integer('informational_quantity')->default(0);
            $table->boolean('viewed_by_admin')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
