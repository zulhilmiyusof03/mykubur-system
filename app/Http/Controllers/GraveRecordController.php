<?php

namespace App\Http\Controllers;

use App\Models\GraveRecord;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class GraveRecordController extends Controller
{
    private const DEFAULT_ROW_COUNT = 57;

    public function index(): JsonResponse
    {
        return response()->json(
            GraveRecord::with('waris')
                ->orderBy('blok')
                ->orderBy('baris')
                ->orderBy('lot')
                ->get()
        );
    }

    public function search(Request $request): JsonResponse
    {
        $query = trim((string) $request->query('q', ''));

        if ($query === '') {
            return response()->json([]);
        }

        $records = GraveRecord::with('waris')
            ->where('no_ic', 'like', "%{$query}%")
            ->orWhereHas('waris', fn ($warisQuery) => $warisQuery->where('nama', 'like', "%{$query}%"))
            ->orderBy('blok')
            ->orderBy('baris')
            ->orderBy('lot')
            ->get()
            ->map(fn (GraveRecord $record) => [
                'id' => $record->id,
                'nama_si_mati' => $record->nama_si_mati,
                'nama_waris' => $record->waris->pluck('nama')->values(),
                'no_ic' => $record->no_ic,
                'blok' => $record->blok,
                'nombor_lot' => "{$record->blok}{$record->baris}-{$record->lot}",
                'lokasi_zon' => $this->zoneName($record->blok),
                'tarikh_kebumi' => $record->tarikh_kebumi,
                'masa_kebumi' => $record->masa_kebumi,
                'waris' => $record->waris,
            ]);

        return response()->json($records);
    }

    public function capacities(): JsonResponse
    {
        $this->ensureBlockLayoutTable();

        return response()->json($this->blockCapacities());
    }

    public function addRows(Request $request): JsonResponse
    {
        $this->ensureBlockLayoutTable();

        $data = $request->validate([
            'blok' => ['required', Rule::in(['A', 'B', 'C'])],
            'row_number' => ['nullable', 'integer', 'min:1', 'max:999'],
            'rows_to_add' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $rows = $this->activeRows($data['blok']);
        $rowsToAdd = (int) ($data['rows_to_add'] ?? 0);
        $rowNumber = (int) ($data['row_number'] ?? 0);

        if ($rowNumber < 1 && $rowsToAdd < 1) {
            return response()->json([
                'message' => 'Sila pilih nombor baris yang sah untuk ditambah.',
                'errors' => ['row_number' => ['Sila pilih nombor baris yang sah untuk ditambah.']],
            ], 422);
        }

        $newRows = [];

        if ($rowNumber > 0) {
            if (in_array($rowNumber, $rows, true)) {
                return response()->json([
                    'message' => "Baris {$rowNumber} Blok {$data['blok']} sudah wujud.",
                    'errors' => ['row_number' => ["Baris {$rowNumber} Blok {$data['blok']} sudah wujud."]],
                ], 422);
            }

            $newRows[] = $rowNumber;
        } else {
            $nextRow = max($rows ?: [self::DEFAULT_ROW_COUNT]) + 1;
            for ($i = 0; $i < $rowsToAdd; $i++) {
                $newRows[] = $nextRow + $i;
            }
        }

        foreach ($newRows as $newRow) {
            $this->upsertBlockRow($data['blok'], $newRow);
        }

        $this->syncBlockLayout($data['blok']);

        $activeRows = $this->activeRows($data['blok']);

        $message = count($newRows) === 1
            ? "Baris {$newRows[0]} Blok {$data['blok']} berjaya ditambah."
            : count($newRows).' baris berjaya ditambah pada Blok '.$data['blok'].'.';

        return response()->json([
            'message' => $message,
            'block' => $data['blok'],
            'row_number' => $newRows[0],
            'row_count' => count($activeRows),
            'max_row' => max($activeRows),
            'rows' => $activeRows,
            'capacities' => $this->blockCapacities(),
        ]);
    }

    public function deleteRow(Request $request): JsonResponse
    {
        $this->ensureBlockLayoutTable();

        $data = $request->validate([
            'blok' => ['required', Rule::in(['A', 'B', 'C'])],
            'row_number' => ['required', 'integer', 'min:1', 'max:999'],
        ]);

        $rows = $this->activeRows($data['blok']);
        $rowNumber = (int) $data['row_number'];

        if (! in_array($rowNumber, $rows, true)) {
            return response()->json([
                'message' => "Baris {$rowNumber} Blok {$data['blok']} tidak wujud.",
                'errors' => ['row_number' => ["Baris {$rowNumber} Blok {$data['blok']} tidak wujud."]],
            ], 422);
        }

        if (count($rows) <= 1) {
            return response()->json([
                'message' => "Blok {$data['blok']} mesti mempunyai sekurang-kurangnya satu baris.",
                'errors' => ['row_number' => ["Blok {$data['blok']} mesti mempunyai sekurang-kurangnya satu baris."]],
            ], 422);
        }

        $hasRecords = GraveRecord::query()
            ->where('blok', $data['blok'])
            ->where('baris', $rowNumber)
            ->exists();

        if ($hasRecords) {
            return response()->json([
                'message' => "Baris {$rowNumber} Blok {$data['blok']} masih mempunyai rekod kubur.",
                'errors' => ['row_number' => ["Baris {$rowNumber} Blok {$data['blok']} masih mempunyai rekod kubur."]],
            ], 422);
        }

        DB::table('grave_block_rows')
            ->where('blok', $data['blok'])
            ->where('row_number', $rowNumber)
            ->delete();

        $this->syncBlockLayout($data['blok']);

        $activeRows = $this->activeRows($data['blok']);

        return response()->json([
            'message' => "Baris {$rowNumber} Blok {$data['blok']} berjaya dipadam.",
            'block' => $data['blok'],
            'row_number' => $rowNumber,
            'row_count' => count($activeRows),
            'max_row' => max($activeRows),
            'rows' => $activeRows,
            'capacities' => $this->blockCapacities(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request);

        $record = DB::transaction(function () use ($data) {
            $waris = $data['waris'];
            unset($data['waris']);

            $record = GraveRecord::create($data);
            $record->waris()->createMany($waris);

            return $record->load('waris');
        });

        return response()->json($record, 201);
    }

    public function update(Request $request, GraveRecord $graveRecord): JsonResponse
    {
        $data = $this->validated($request, $graveRecord->id);

        $record = DB::transaction(function () use ($data, $graveRecord) {
            $waris = $data['waris'];
            unset($data['waris']);

            $graveRecord->update($data);
            $graveRecord->waris()->delete();
            $graveRecord->waris()->createMany($waris);

            return $graveRecord->load('waris');
        });

        return response()->json($record);
    }

    public function destroy(GraveRecord $graveRecord): JsonResponse
    {
        $graveRecord->delete();

        return response()->json(['message' => 'Rekod berjaya dipadam.']);
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $validator = Validator::make($request->all(), [
            'nama_si_mati' => ['required', 'string', 'max:255'],
            'no_ic' => [
                'required',
                'string',
                'max:255',
                Rule::unique('grave_records', 'no_ic')->ignore($ignoreId),
            ],
            'blok' => ['required', Rule::in(['A', 'B', 'C'])],
            'baris' => ['required', 'integer', 'min:1'],
            'lot' => [
                'required',
                'integer',
                'min:1',
                Rule::unique('grave_records', 'lot')
                    ->where(fn ($query) => $query
                        ->where('blok', $request->input('blok'))
                        ->where('baris', $request->input('baris')))
                    ->ignore($ignoreId),
            ],
            'tarikh_kebumi' => ['required', 'date'],
            'masa_kebumi' => ['required', 'date_format:H:i'],
            'waris' => ['required', 'array', 'min:1', 'max:5'],
            'waris.*.nama' => ['required', 'string', 'max:255'],
            'waris.*.no_tel' => ['required', 'string', 'max:50'],
        ]);

        $validator->after(function ($validator) use ($request) {
            $blok = $request->input('blok');
            $baris = (int) $request->input('baris');

            if (in_array($blok, ['A', 'B', 'C'], true) && ! $this->rowExists($blok, $baris)) {
                $validator->errors()->add('baris', 'Baris ini belum wujud untuk blok yang dipilih.');
                return;
            }

            if (! $this->isValidSlot($request->input('blok'), $request->input('baris'), $request->input('lot'))) {
                $validator->errors()->add('lot', 'Baris atau blok tidak sah untuk susun atur kubur ini.');
            }
        });

        return $validator->validate();
    }

    private function isValidSlot(?string $blok, mixed $baris, mixed $lot): bool
    {
        if (! in_array($blok, ['A', 'B', 'C'], true)) {
            return false;
        }

        $baris = (int) $baris;
        $lot = (int) $lot;

        if ($baris < 1 || ! $this->rowExists($blok, $baris)) {
            return false;
        }

        return $lot >= 1;
    }

    private function blockCapacities(): array
    {
        return collect(['A', 'B', 'C'])
            ->mapWithKeys(fn (string $blok) => [
                $blok => [
                    'block' => $blok,
                    'row_count' => count($this->activeRows($blok)),
                    'max_row' => $this->rowCount($blok),
                    'rows' => $this->activeRows($blok),
                    'total_capacity' => $this->calculateBlockCapacity($blok),
                ],
            ])
            ->all();
    }

    private function calculateBlockCapacity(string $blok): int
    {
        $capacity = 0;

        foreach ($this->activeRows($blok) as $baris) {
            $baseLotCount = $this->baseLotCount($blok, $baris);
            $highestLot = (int) GraveRecord::query()
                ->where('blok', $blok)
                ->where('baris', $baris)
                ->max('lot');

            $capacity += max($baseLotCount, $highestLot);
        }

        return $capacity;
    }

    private function baseLotCount(string $blok, int $baris): int
    {
        return $blok === 'B' && $baris <= 7 ? 5 : 10;
    }

    private function rowCount(string $blok): int
    {
        $configuredMaxRow = 0;

        if (Schema::hasTable('grave_block_layouts')) {
            $configuredMaxRow = (int) DB::table('grave_block_layouts')
                ->where('blok', $blok)
                ->value('row_count');
        }

        $highestUsedRow = (int) GraveRecord::query()
            ->where('blok', $blok)
            ->max('baris');

        $activeRows = Schema::hasTable('grave_block_rows') ? $this->activeRows($blok) : [];
        $highestActiveRow = $activeRows === [] ? 0 : max($activeRows);

        return max(self::DEFAULT_ROW_COUNT, $configuredMaxRow, $highestUsedRow, $highestActiveRow);
    }

    private function ensureBlockLayoutTable(): void
    {
        if (! Schema::hasTable('grave_block_layouts')) {
            Schema::create('grave_block_layouts', function (Blueprint $table) {
                $table->id();
                $table->char('blok', 1)->unique();
                $table->unsignedSmallInteger('row_count')->default(self::DEFAULT_ROW_COUNT);
                $table->timestamps();
            });
        }

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
            if (DB::table('grave_block_rows')->where('blok', $blok)->doesntExist()) {
                $rowCount = max(self::DEFAULT_ROW_COUNT, $this->configuredLayoutRows($blok), $this->highestUsedRow($blok));

                for ($rowNumber = 1; $rowNumber <= $rowCount; $rowNumber++) {
                    $this->upsertBlockRow($blok, $rowNumber);
                }
            }

            GraveRecord::query()
                ->where('blok', $blok)
                ->select('baris')
                ->distinct()
                ->pluck('baris')
                ->each(fn ($rowNumber) => $this->upsertBlockRow($blok, (int) $rowNumber));

            $this->syncBlockLayout($blok);
        }
    }

    private function activeRows(string $blok): array
    {
        if (! Schema::hasTable('grave_block_rows')) {
            return range(1, max(self::DEFAULT_ROW_COUNT, $this->configuredLayoutRows($blok), $this->highestUsedRow($blok)));
        }

        $rows = DB::table('grave_block_rows')
            ->where('blok', $blok)
            ->orderBy('row_number')
            ->pluck('row_number')
            ->map(fn ($rowNumber) => (int) $rowNumber)
            ->all();

        return $rows === []
            ? range(1, max(self::DEFAULT_ROW_COUNT, $this->configuredLayoutRows($blok), $this->highestUsedRow($blok)))
            : $rows;
    }

    private function rowExists(string $blok, int $baris): bool
    {
        return in_array($baris, $this->activeRows($blok), true);
    }

    private function configuredLayoutRows(string $blok): int
    {
        if (! Schema::hasTable('grave_block_layouts')) {
            return 0;
        }

        return (int) DB::table('grave_block_layouts')
            ->where('blok', $blok)
            ->value('row_count');
    }

    private function highestUsedRow(string $blok): int
    {
        return (int) GraveRecord::query()
            ->where('blok', $blok)
            ->max('baris');
    }

    private function upsertBlockRow(string $blok, int $rowNumber): void
    {
        DB::table('grave_block_rows')->upsert(
            [[
                'blok' => $blok,
                'row_number' => $rowNumber,
                'updated_at' => now(),
                'created_at' => now(),
            ]],
            ['blok', 'row_number'],
            ['updated_at']
        );
    }

    private function syncBlockLayout(string $blok): void
    {
        $rows = $this->activeRows($blok);

        DB::table('grave_block_layouts')->upsert(
            [[
                'blok' => $blok,
                'row_count' => count($rows),
                'updated_at' => now(),
                'created_at' => now(),
            ]],
            ['blok'],
            ['row_count', 'updated_at']
        );
    }

    private function zoneName(string $blok): string
    {
        return match ($blok) {
            'A' => 'Zon Kanan',
            'B' => 'Zon Tengah',
            default => 'Zon Kiri',
        };
    }
}
