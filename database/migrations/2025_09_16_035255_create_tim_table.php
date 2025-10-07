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
        Schema::create('tim', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 225);
            $table->string('no_telp', 225)->nullable();
            $table->string('atm', 225)->nullable();
            $table->integer('norek')->nullable();
            $table->bigInteger('gaji')->nullable();
            $table->bigInteger('total_potongan_cicilan')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tim');
    }
};
