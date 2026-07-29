<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pre_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('po_code')->unique();
            $table->enum('status', ['pending', 'accepted', 'rejected', 'processing', 'completed', 'cancelled'])->default('pending');
            $table->text('notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->unsignedInteger('estimated_days')->nullable()->comment('Estimasi hari pengerjaan, diset admin');

            // Shipping info (filled by user at creation)
            $table->string('shipping_name');
            $table->string('shipping_phone');
            $table->text('shipping_address');
            $table->string('shipping_province')->nullable();
            $table->string('shipping_city')->nullable();
            $table->string('shipping_district')->nullable();
            $table->string('shipping_village')->nullable();
            $table->string('shipping_postal_code')->nullable();
            $table->string('city_id')->nullable();

            // Courier info (filled after PO accepted & user selects shipping)
            $table->string('courier')->nullable();
            $table->string('courier_service')->nullable();
            $table->unsignedBigInteger('shipping_cost')->nullable()->default(0);

            // Payment
            $table->string('payment_method')->nullable();
            $table->unsignedBigInteger('total_amount')->default(0);

            $table->timestamps();
        });

        Schema::create('pre_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pre_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('product_name');
            $table->unsignedInteger('qty');
            $table->unsignedBigInteger('price');
            $table->unsignedBigInteger('subtotal');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pre_order_items');
        Schema::dropIfExists('pre_orders');
    }
};
