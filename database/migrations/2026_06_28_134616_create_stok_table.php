<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stok', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('barang_id');
            $table->unsignedBigInteger('gudang_id');
            $table->unsignedInteger('saldo')->default(0); // unsignedInteger to prevent negative values at DB level
            $table->enum('status', ['aman', 'menipis', 'kritis', 'belum_dikonfigurasi'])->default('belum_dikonfigurasi');
            $table->timestamps();

            $table->unique(['barang_id', 'gudang_id']);
            $table->foreign('barang_id')->references('id')->on('barang')->onDelete('cascade');
            $table->foreign('gudang_id')->references('id')->on('gudang')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stok');
    }
};
