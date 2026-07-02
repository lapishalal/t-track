<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('upload_logs', function (Blueprint $table) {
            $table->id();
            $table->string('file_name');
            $table->enum('file_type', ['order', 'income']);
            $table->string('shop_name');
            $table->integer('total_rows_imported')->default(0);
            $table->foreignId('uploaded_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('upload_logs');
    }
};