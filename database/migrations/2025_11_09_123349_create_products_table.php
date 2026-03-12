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
            $table->string('sku')->unique()->nullable();
            $table->json('name')->nullable();
            $table->json('short_description')->nullable();
            $table->json('description')->nullable();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status')->default(ProductStatus::ACTIVE->value);
            $table->boolean('is_global')->default(true)->nullable();
            $table->boolean('has_custom_markup_fee')->default(false)->nullable();
            $table->enum('custom_markup_fee_type', ['fixed', 'percentage'])->nullable();
            $table->decimal('custom_markup_fee_value', 10, 2)->default(0)->nullable();
            $table->boolean('has_variants')->default(false)->nullable();
            $table->decimal('provider_original_price', 10, 2)->nullable();
            $table->decimal('base_price', 10, 2)->nullable();
            $table->boolean('has_discount')->default(false);
            $table->enum('discount_type', ['fixed', 'percentage'])->nullable();
            $table->decimal('discount_value', 10, 2)->default(0)->nullable();
            $table->decimal('final_price', 10, 2)->nullable();
            $table->enum('delivery_type', ['instant', 'requires_confirmation'])->nullable();
            $table->boolean('marked_as_out_of_stock')->default(false);
            $table->integer('manual_stock')->default(0);
            $table->boolean('viewed_by_admin')->default(false);
            $table->boolean('api_stock_available')->nullable();
            $table->timestamp('api_stock_last_checked_at')->nullable();
            $table->boolean('is_best_seller')->default(false);
            $table->boolean('is_popular')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_trending')->default(false);
            $table->decimal('avg_rating')->nullable();
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
