<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_claims', function (Blueprint $table) {
            $table->id();
            $table->string('order_id')->index();
            $table->string('tracking_id')->nullable();
            $table->decimal('shipping_fee_estimated', 15, 2)->default(0);
            $table->decimal('shipping_fee_real', 15, 2)->default(0);
            $table->decimal('selisih_rugi', 15, 2)->default(0);
            $table->string('ekspedisi')->nullable(); // J&T, JNE, SiCepat, dll
            $table->string('ticket_number')->nullable(); // Nomor tiket klaim
            $table->enum('status', ['belum_diklaim', 'proses_klaim', 'berhasil', 'ditolak'])->default('belum_diklaim');
            $table->date('tanggal_klaim')->nullable();
            $table->text('keterangan')->nullable();
            $table->string('created_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_claims');
    }
};
