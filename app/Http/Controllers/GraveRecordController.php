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
            'rows_to_add' => ['required', 'integer', 'min:1', 'max:50'],
        ]);

        $currentRows = $this->rowCount($data['blok']);
        $newRows = $currentRows + (int) $data['rows_to_add'];

        DB::table('grave_block_layouts')->updateOrInsert(
            ['blok' => $data['blok']],
            [
                'row_count' => $newRows,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        return response()->json([
            'message' => "Baris Blok {$data['blok']} berjaya ditambah.",
            'block' => $data['blok'],
            'row_count' => $newRows,
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

            if (in_array($blok, ['A', 'B', 'C'], true) && $baris > $this->rowCount($blok)) {
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

        if ($baris < 1 || $baris > $this->rowCount($blok)) {
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
                    'row_count' => $this->rowCount($blok),
                    'total_capacity' => $this->calculateBlockCapacity($blok),
                ],
            ])
            ->all();
    }

    private function calculateBlockCapacity(string $blok): int
    {
        $capacity = 0;

        for ($baris = 1; $baris <= $this->rowCount($blok); $baris++) {
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
        $configuredRows = 0;

        if (Schema::hasTable('grave_block_layouts')) {
            $configuredRows = (int) DB::table('grave_block_layouts')
                ->where('blok', $blok)
                ->value('row_count');
        }

        $highestUsedRow = (int) GraveRecord::query()
            ->where('blok', $blok)
            ->max('baris');

        return max(self::DEFAULT_ROW_COUNT, $configuredRows, $highestUsedRow);
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

        foreach (['A', 'B', 'C'] as $blok) {
            DB::table('grave_block_layouts')->updateOrInsert(
                ['blok' => $blok],
                [
                    'row_count' => max(self::DEFAULT_ROW_COUNT, $this->rowCount($blok)),
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
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
