<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_costs', function (Blueprint $table) {
            $table->id();
            $table->string('sku_id')->unique();
            $table->string('shop_name')->index();
            $table->text('product_name');
            $table->string('variation')->nullable();
            $table->decimal('hpp_amount', 15, 2)->default(0);
            $table->decimal('overhead_per_pack', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_costs');
    }
};