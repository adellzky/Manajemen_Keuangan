<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
       public function up(): void
    {
        Schema::create('pengeluaran', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_project')->nullable();
            $table->bigInteger('jumlah');
            $table->date('tanggal')->nullable();
            $table->string('nama_project_manual', 225)->nullable();
            $table->enum('sumber_dana', ['cash', 'bank'])->default('bank');
            $table->string('keterangan', 225)->nullable();
            $table->timestamps();

            $table->foreign('id_project')
                ->references('id')
                ->on('projects')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengeluaran');
    }
};
