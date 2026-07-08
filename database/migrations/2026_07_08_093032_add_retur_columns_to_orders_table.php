<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('retur_moved_at')->nullable()->after('hidden_at');
            $table->timestamp('retur_completed_at')->nullable()->after('retur_moved_at');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['retur_moved_at', 'retur_completed_at']);
        });
    }
};
