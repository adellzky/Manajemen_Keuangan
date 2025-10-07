<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cicilan_hutang', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_hutang');
            $table->unsignedBigInteger('id_gaji')->nullable();
            $table->bigInteger('nominal_cicilan');
            $table->date('tanggal_bayar');
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->foreign('id_hutang')->references('id')->on('hutang')->onDelete('cascade');
            $table->foreign('id_gaji')->references('id')->on('gaji')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cicilan_hutang');
    }
};

