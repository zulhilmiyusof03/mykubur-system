<?php

namespace App\Http\Controllers;

use App\Models\GraveRecord;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class GraveRecordController extends Controller
{
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

    public function resetDemo(Request $request): JsonResponse
    {
        $records = $request->validate([
            'records' => ['required', 'array'],
            'records.*.nama_si_mati' => ['required', 'string', 'max:255'],
            'records.*.no_ic' => ['required', 'string', 'max:255'],
            'records.*.blok' => ['required', Rule::in(['A', 'B', 'C'])],
            'records.*.baris' => ['required', 'integer', 'between:1,100'],
            'records.*.lot' => ['required', 'integer', 'between:1,20'],
            'records.*.tarikh_kebumi' => ['required', 'date'],
            'records.*.masa_kebumi' => ['required', 'date_format:H:i'],
            'records.*.waris' => ['required', 'array', 'min:1', 'max:5'],
            'records.*.waris.*.nama' => ['required', 'string', 'max:255'],
            'records.*.waris.*.no_tel' => ['required', 'string', 'max:50'],
        ])['records'];

        DB::transaction(function () use ($records) {
            GraveRecord::query()->delete();

            foreach ($records as $data) {
                $waris = $data['waris'];
                unset($data['id'], $data['waris']);

                $record = GraveRecord::create($data);
                $record->waris()->createMany($waris);
            }
        });

        return $this->index();
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'nama_si_mati' => ['required', 'string', 'max:255'],
            'no_ic' => [
                'required',
                'string',
                'max:255',
                Rule::unique('grave_records', 'no_ic')->ignore($ignoreId),
            ],
            'blok' => ['required', Rule::in(['A', 'B', 'C'])],
            'baris' => ['required', 'integer', 'between:1,100'],
            'lot' => [
                'required',
                'integer',
                'between:1,20',
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
    }
}
