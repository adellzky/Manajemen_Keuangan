<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('hutang', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_tim');
            $table->bigInteger('jumlah_hutang');
            $table->date('tanggal_pinjam');
            $table->bigInteger('sisa_hutang');
            $table->text('keterangan')->nullable();
            $table->enum('status', ['Belum Lunas', 'Lunas'])->default('Belum Lunas');
            $table->timestamps();

            $table->foreign('id_tim')->references('id')->on('tim')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hutang');
    }
};

