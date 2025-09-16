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
        Schema::create('gaji', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_tim');
            $table->unsignedBigInteger('id_project');
            $table->integer('jumlah');
            $table->date('tanggal')->nullable();
            $table->enum('metode_bayar', ['tf', 'cash'])->default('tf');
            $table->timestamps();

            $table->foreign('id_tim')->references('id')->on('tim')->onDelete('cascade');
            $table->foreign('id_project')->references('id')->on('projects')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gaji');
    }
};
