<?php

namespace Database\Seeders;

use App\Models\GraveRecord;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $records = [
            [
                'nama_si_mati' => 'Ahmad bin Musa',
                'no_ic' => '650203-08-5431',
                'blok' => 'A',
                'baris' => 1,
                'lot' => 1,
                'tarikh_kebumi' => '2026-01-15',
                'masa_kebumi' => '10:30',
                'waris' => [
                    ['nama' => 'Mohd Musa bin Ahmad', 'no_tel' => '012-3456789'],
                    ['nama' => 'Aminah binti Ahmad', 'no_tel' => '019-8765432'],
                ],
            ],
            [
                'nama_si_mati' => 'Ali bin Talib',
                'no_ic' => '780512-02-3321',
                'blok' => 'A',
                'baris' => 1,
                'lot' => 2,
                'tarikh_kebumi' => '2026-02-20',
                'masa_kebumi' => '14:15',
                'waris' => [
                    ['nama' => 'Fatimah binti Talib', 'no_tel' => '011-23456789'],
                ],
            ],
            [
                'nama_si_mati' => 'Siti Aminah binti Yusof',
                'no_ic' => '520814-10-5566',
                'blok' => 'B',
                'baris' => 5,
                'lot' => 12,
                'tarikh_kebumi' => '2026-03-01',
                'masa_kebumi' => '09:00',
                'waris' => [
                    ['nama' => 'Khairul bin Yusof', 'no_tel' => '013-4445555'],
                ],
            ],
            [
                'nama_si_mati' => 'Kassim bin Selamat',
                'no_ic' => '450912-01-4433',
                'blok' => 'C',
                'baris' => 99,
                'lot' => 20,
                'tarikh_kebumi' => '2025-12-10',
                'masa_kebumi' => '11:00',
                'waris' => [
                    ['nama' => 'Sabariah binti Kassim', 'no_tel' => '017-9998888'],
                ],
            ],
        ];

        foreach ($records as $data) {
            $waris = $data['waris'];
            unset($data['waris']);

            $record = GraveRecord::firstOrCreate(
                ['blok' => $data['blok'], 'baris' => $data['baris'], 'lot' => $data['lot']],
                $data
            );

            if ($record->waris()->doesntExist()) {
                $record->waris()->createMany($waris);
            }
        }
    }
}
