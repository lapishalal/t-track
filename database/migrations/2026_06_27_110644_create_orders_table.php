<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_id')->unique();
            $table->string('sku_id')->nullable()->index();
            $table->string('shop_name')->index();
            $table->string('order_status');
            $table->text('product_name');
            $table->string('variation')->nullable();
            $table->integer('quantity');
            $table->decimal('order_amount', 15, 2);
            $table->decimal('shipping_fee_estimated', 15, 2)->default(0);
            $table->string('buyer_username')->nullable();
            $table->string('province')->nullable();
            $table->string('regency_city')->nullable();
            $table->string('tracking_id')->nullable();
            $table->timestamp('created_time')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};