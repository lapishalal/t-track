<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('operator')->after('password')->index();
        });

        DB::table('users')->orderBy('id')->limit(1)->update(['role' => 'owner']);

        Schema::create('sales_targets', function (Blueprint $table) {
            $table->id();
            $table->string('shop_name')->index();
            $table->date('target_month')->index();
            $table->decimal('target_amount', 15, 2)->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['shop_name', 'target_month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_targets');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
