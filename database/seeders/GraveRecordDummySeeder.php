<?php

namespace Database\Seeders;

use App\Models\GraveRecord;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GraveRecordDummySeeder extends Seeder
{
    public function run(): void
    {
        $records = [
            ['nama_si_mati' => 'Haji Abdullah bin Salleh', 'no_ic' => '460112-10-4101', 'blok' => 'A', 'baris' => 12, 'lot' => 4, 'tarikh_kebumi' => '2026-06-03', 'masa_kebumi' => '09:20', 'waris' => [['nama' => 'Noraini binti Abdullah', 'no_tel' => '012-4101001']]],
            ['nama_si_mati' => 'Rohani binti Karim', 'no_ic' => '520728-08-4102', 'blok' => 'A', 'baris' => 32, 'lot' => 7, 'tarikh_kebumi' => '2026-06-18', 'masa_kebumi' => '11:10', 'waris' => [['nama' => 'Azman bin Karim', 'no_tel' => '013-4101002']]],
            ['nama_si_mati' => 'Ismail bin Latif', 'no_ic' => '490315-10-4201', 'blok' => 'B', 'baris' => 6, 'lot' => 3, 'tarikh_kebumi' => '2026-06-09', 'masa_kebumi' => '10:00', 'waris' => [['nama' => 'Maznah binti Ismail', 'no_tel' => '014-4201001']]],
            ['nama_si_mati' => 'Zainab binti Omar', 'no_ic' => '570902-10-4202', 'blok' => 'B', 'baris' => 15, 'lot' => 4, 'tarikh_kebumi' => '2026-06-21', 'masa_kebumi' => '15:30', 'waris' => [['nama' => 'Fahmi bin Omar', 'no_tel' => '017-4201002']]],
            ['nama_si_mati' => 'Mariam binti Yusof', 'no_ic' => '600104-08-4301', 'blok' => 'C', 'baris' => 8, 'lot' => 9, 'tarikh_kebumi' => '2026-06-12', 'masa_kebumi' => '08:45', 'waris' => [['nama' => 'Hafiz bin Yusof', 'no_tel' => '018-4301001']]],
            ['nama_si_mati' => 'Kassim bin Selamat', 'no_ic' => '450912-01-4302', 'blok' => 'C', 'baris' => 44, 'lot' => 2, 'tarikh_kebumi' => '2026-06-27', 'masa_kebumi' => '14:05', 'waris' => [['nama' => 'Sabariah binti Kassim', 'no_tel' => '019-4301002']]],
        ];

        DB::transaction(function () use ($records) {
            GraveRecord::query()->delete();

            foreach ($records as $data) {
                $waris = $data['waris'];
                unset($data['waris']);

                $record = GraveRecord::create($data);
                $record->waris()->createMany($waris);
            }
        });
    }
}
