<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grave_waris', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grave_record_id')->constrained()->cascadeOnDelete();
            $table->string('nama');
            $table->string('no_tel');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grave_waris');
    }
};
