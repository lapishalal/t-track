<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_cost_histories', function (Blueprint $table) {
            $table->id();
            $table->string('sku_id')->index();
            $table->string('shop_name')->nullable();
            $table->text('product_name')->nullable();
            $table->decimal('hpp_amount_old', 15, 2)->default(0);
            $table->decimal('hpp_amount_new', 15, 2)->default(0);
            $table->decimal('overhead_per_pack_old', 15, 2)->default(0);
            $table->decimal('overhead_per_pack_new', 15, 2)->default(0);
            $table->string('changed_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_cost_histories');
    }
};