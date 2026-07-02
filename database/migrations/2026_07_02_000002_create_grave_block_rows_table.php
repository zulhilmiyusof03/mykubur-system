<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('grave_block_rows')) {
            Schema::create('grave_block_rows', function (Blueprint $table) {
                $table->id();
                $table->char('blok', 1);
                $table->unsignedSmallInteger('row_number');
                $table->timestamps();

                $table->unique(['blok', 'row_number']);
            });
        }

        foreach (['A', 'B', 'C'] as $blok) {
            $rowCount = (int) DB::table('grave_block_layouts')
                ->where('blok', $blok)
                ->value('row_count');

            $rowCount = max(57, $rowCount);

            for ($rowNumber = 1; $rowNumber <= $rowCount; $rowNumber++) {
                DB::table('grave_block_rows')->upsert(
                    [[
                        'blok' => $blok,
                        'row_number' => $rowNumber,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]],
                    ['blok', 'row_number'],
                    ['updated_at']
                );
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('grave_block_rows');
    }
};
