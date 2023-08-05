<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		// order_subtotal = product_amount (with discounts) * quantity
		// product_subtotal = (product1_price * quantity) + (product2_price * quantity)
		Schema::create('orders', function (Blueprint $table) {
			$table->id();
			$table->unsignedBigInteger('user_id')->nullable();
			$table->string('status', 50)->nullable();
			$table->string('currency', 10)->nullable();
			// Order total cost
			$table->decimal('cost', 26, 2)->nullable()->default(0.00);
			// Products cost after discounts
			$table->decimal('products_subtotal', 26, 2)->nullable()->default(0.00);
			// Product addons cost
			$table->decimal('addons_subtotal', 26, 2)->nullable()->default(0.00);
			// Shipping costs for products (etc. packaging)
			$table->decimal('shipping_subtotal', 26, 2)->nullable()->default(0.00);
			// Discount costs for products
			$table->decimal('discount_subtotal', 26, 2)->nullable()->default(0.00);
			// Shipping, delivery cost
			$table->decimal('shipping_amount', 26, 2)->nullable()->default(0.00);
			// Methods: home, pickup, restaurant
			$table->enum('shipping_method', ['delivery', 'pickup', 'restaurant'])->nullable()->default('delivery');
			// Home address
			$table->string('shipping_country', 50)->nullable();
			$table->string('shipping_state', 50)->nullable();
			$table->string('shipping_city', 50)->nullable();
			$table->string('shipping_street')->nullable();
			$table->string('shipping_postal_code', 50)->nullable();
			$table->string('shipping_comment')->nullable();
			$table->string('shipping_mobile')->nullable();
			$table->string('shipping_email')->nullable();
			$table->timestamp('shipping_time')->nullable();
			// Payment online
			$table->uuid('payment_id')->nullable()->default(Str::uuid());
			$table->enum('payment_method', ['money', 'card', 'online', 'cashback'])->nullable()->default('money');
			$table->string('payment_gateway', 50)->nullable();
			// Dates
			$table->timestamps();
			$table->softDeletes();
			// Relatons
			$table->foreign('user_id')
				->references('id')
				->on('users')
				->cascadeOnUpdate();
			// ->nullOnDelete();
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists('orders');
	}
};
