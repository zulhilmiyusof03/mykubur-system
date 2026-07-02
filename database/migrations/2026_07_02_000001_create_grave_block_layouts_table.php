<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grave_block_layouts', function (Blueprint $table) {
            $table->id();
            $table->char('blok', 1)->unique();
            $table->unsignedSmallInteger('row_count')->default(57);
            $table->timestamps();
        });

        foreach (['A', 'B', 'C'] as $blok) {
            DB::table('grave_block_layouts')->insert([
                'blok' => $blok,
                'row_count' => 57,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('grave_block_layouts');
    }
};
