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
        Schema::table('gaji', function (Blueprint $table) {
            // drop foreign key dulu
            $table->dropForeign(['id_project']);
        });

        Schema::table('gaji', function (Blueprint $table) {
            // ubah jadi nullable
            $table->unsignedBigInteger('id_project')->nullable()->change();
        });

        Schema::table('gaji', function (Blueprint $table) {
            // tambahkan lagi foreign key
            $table->foreign('id_project')->references('id')->on('projects')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('gaji', function (Blueprint $table) {
            $table->dropForeign(['id_project']);
        });

        Schema::table('gaji', function (Blueprint $table) {
            $table->unsignedBigInteger('id_project')->nullable(false)->change();
        });

        Schema::table('gaji', function (Blueprint $table) {
            $table->foreign('id_project')->references('id')->on('projects')->onDelete('cascade');
        });
    }
};
