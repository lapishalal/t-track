<?php

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
        // 1. Tambah batch_id di tabel upload_logs
        Schema::table('upload_logs', function (Blueprint $table) {
            $table->string('batch_id')->nullable()->after('id')->index();
        });

        // 2. Tambah batch_id di tabel orders
        Schema::table('orders', function (Blueprint $table) {
            $table->string('batch_id')->nullable()->after('id')->index();
        });

        // 3. Tambah batch_id di tabel incomes
        Schema::table('incomes', function (Blueprint $table) {
            $table->string('batch_id')->nullable()->after('id')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('upload_logs', function (Blueprint $table) {
            $table->dropColumn('batch_id');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('batch_id');
        });

        Schema::table('incomes', function (Blueprint $table) {
            $table->dropColumn('batch_id');
        });
    }
};