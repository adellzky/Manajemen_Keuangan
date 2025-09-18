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
        Schema::create('pengeluaran', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_project');
            $table->bigInteger('jumlah');
            $table->date('tanggal')->nullable();
            $table->string('keterangan', 225)->nullable();
            $table->bigInteger('total_pengeluaran')->nullable();
            $table->timestamps();

            $table->foreign('id_project')->references('id')->on('projects')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengeluaran');
    }
};
