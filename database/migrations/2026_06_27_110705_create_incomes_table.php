<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incomes', function (Blueprint $table) {
            $table->id();
            $table->string('order_id')->index();
            $table->string('shop_name')->index();
            $table->string('transaction_type');
            $table->decimal('disbursement_amount', 15, 2)->default(0);
            $table->decimal('total_revenue', 15, 2)->default(0);
            $table->decimal('platform_commission_fee', 15, 2)->default(0);
            $table->decimal('payment_fee', 15, 2)->default(0);
            $table->decimal('affiliate_commission', 15, 2)->default(0);
            $table->decimal('shipping_fee_real', 15, 2)->default(0);
            $table->timestamp('payout_time')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incomes');
    }
};