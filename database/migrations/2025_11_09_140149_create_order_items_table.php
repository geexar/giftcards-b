<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->string('item_no');
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_variant_value_id')->nullable()->constrained()->nullOnDelete();
            $table->string('delivery_type')->nullable();
            $table->decimal('provider_original_price', 10, 2)->nullable();
            $table->decimal('price', 10, 2);
            $table->string('markup_fee_origin');
            $table->enum('markup_fee_type', ['fixed', 'percentage']);
            $table->decimal('markup_fee_value', 10, 2);
            $table->decimal('user_facing_price', 10, 2);
            $table->integer('quantity');
            $table->decimal('total', 10, 2)->nullable();
            $table->integer('fulfilled_quantity')->nullable();
            $table->string('status')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
