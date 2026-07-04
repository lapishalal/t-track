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
        // 1. Tambah batch_id di tabel upload_logs (kalau belum ada)
        if (!Schema::hasColumn('upload_logs', 'batch_id')) {
            Schema::table('upload_logs', function (Blueprint $table) {
                $table->string('batch_id')->nullable()->after('id')->index();
            });
        }

        // 2. Tambah batch_id di tabel orders (kalau belum ada)
        if (!Schema::hasColumn('orders', 'batch_id')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->string('batch_id')->nullable()->after('id')->index();
            });
        }

        // 3. Tambah batch_id di tabel incomes (kalau belum ada)
        if (!Schema::hasColumn('incomes', 'batch_id')) {
            Schema::table('incomes', function (Blueprint $table) {
                $table->string('batch_id')->nullable()->after('id')->index();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('upload_logs', 'batch_id')) {
            Schema::table('upload_logs', function (Blueprint $table) {
                $table->dropColumn('batch_id');
            });
        }

        if (Schema::hasColumn('orders', 'batch_id')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('batch_id');
            });
        }

        if (Schema::hasColumn('incomes', 'batch_id')) {
            Schema::table('incomes', function (Blueprint $table) {
                $table->dropColumn('batch_id');
            });
        }
    }
};
