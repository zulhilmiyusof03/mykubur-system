<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grave_records', function (Blueprint $table) {
            $table->id();
            $table->string('nama_si_mati');
            $table->string('no_ic')->unique();
            $table->char('blok', 1);
            $table->unsignedSmallInteger('baris');
            $table->unsignedSmallInteger('lot');
            $table->date('tarikh_kebumi');
            $table->time('masa_kebumi');
            $table->timestamps();

            $table->unique(['blok', 'baris', 'lot']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grave_records');
    }
};
