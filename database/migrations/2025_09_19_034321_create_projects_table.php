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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_mitra');
            $table->string('nama_project', 225);
            $table->enum('kategori', ['Jasa', 'Produk'])->default('Jasa');
            $table->string('deskripsi', 225)->nullable();
            $table->bigInteger('harga');
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->enum('status', ['Belum','Proses', 'Selesai', 'Batal'])->default('Belum');
            $table->enum('status_bayar', ['Belum', 'Dp', 'Lunas'])->default('Belum');
            $table->timestamps();

            $table->foreign('id_mitra')->references('id')->on('mitra')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
