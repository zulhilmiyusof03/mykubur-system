<?php

namespace Database\Seeders;

use App\Models\GraveRecord;
use App\Models\User;
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
        User::updateOrCreate(
            ['email' => 'admin@mykubur.com'],
            ['name' => 'Pengurus Kubur (Admin)', 'password' => 'admin123', 'role' => 'admin']
        );

        User::updateOrCreate(
            ['email' => 'waris@mykubur.com'],
            ['name' => 'Mohd Waris', 'password' => 'waris123', 'role' => 'waris']
        );

        $records = [
            ['nama_si_mati' => 'Haji Abdullah bin Salleh', 'no_ic' => '460112-10-4101', 'blok' => 'A', 'baris' => 12, 'lot' => 4, 'tarikh_kebumi' => '2026-06-03', 'masa_kebumi' => '09:20', 'waris' => [['nama' => 'Noraini binti Abdullah', 'no_tel' => '012-4101001']]],
            ['nama_si_mati' => 'Rohani binti Karim', 'no_ic' => '520728-08-4102', 'blok' => 'A', 'baris' => 32, 'lot' => 7, 'tarikh_kebumi' => '2026-06-18', 'masa_kebumi' => '11:10', 'waris' => [['nama' => 'Azman bin Karim', 'no_tel' => '013-4101002']]],
            ['nama_si_mati' => 'Mohd Yusri bin Hamid', 'no_ic' => '610221-10-4103', 'blok' => 'A', 'baris' => 5, 'lot' => 2, 'tarikh_kebumi' => '2026-06-22', 'masa_kebumi' => '10:40', 'waris' => [['nama' => 'Faridah binti Mohd Yusri', 'no_tel' => '011-4101003']]],
            ['nama_si_mati' => 'Aminah binti Dolah', 'no_ic' => '540809-10-4104', 'blok' => 'A', 'baris' => 18, 'lot' => 9, 'tarikh_kebumi' => '2026-06-25', 'masa_kebumi' => '16:10', 'waris' => [['nama' => 'Rahman bin Dolah', 'no_tel' => '012-4101004']]],
            ['nama_si_mati' => 'Salleh bin Harun', 'no_ic' => '500430-10-4105', 'blok' => 'A', 'baris' => 47, 'lot' => 1, 'tarikh_kebumi' => '2026-06-29', 'masa_kebumi' => '09:55', 'waris' => [['nama' => 'Hasnah binti Salleh', 'no_tel' => '013-4101005']]],
            ['nama_si_mati' => 'Ismail bin Latif', 'no_ic' => '490315-10-4201', 'blok' => 'B', 'baris' => 6, 'lot' => 3, 'tarikh_kebumi' => '2026-06-09', 'masa_kebumi' => '10:00', 'waris' => [['nama' => 'Maznah binti Ismail', 'no_tel' => '014-4201001']]],
            ['nama_si_mati' => 'Zainab binti Omar', 'no_ic' => '570902-10-4202', 'blok' => 'B', 'baris' => 15, 'lot' => 4, 'tarikh_kebumi' => '2026-06-21', 'masa_kebumi' => '15:30', 'waris' => [['nama' => 'Fahmi bin Omar', 'no_tel' => '017-4201002']]],
            ['nama_si_mati' => 'Rahim bin Osman', 'no_ic' => '630118-10-4203', 'blok' => 'B', 'baris' => 3, 'lot' => 5, 'tarikh_kebumi' => '2026-06-24', 'masa_kebumi' => '12:15', 'waris' => [['nama' => 'Nadia binti Rahim', 'no_tel' => '018-4201003']]],
            ['nama_si_mati' => 'Halimah binti Sulaiman', 'no_ic' => '590706-10-4204', 'blok' => 'B', 'baris' => 22, 'lot' => 8, 'tarikh_kebumi' => '2026-06-26', 'masa_kebumi' => '11:35', 'waris' => [['nama' => 'Syafiq bin Sulaiman', 'no_tel' => '019-4201004']]],
            ['nama_si_mati' => 'Yaakob bin Mat', 'no_ic' => '470527-10-4205', 'blok' => 'B', 'baris' => 51, 'lot' => 6, 'tarikh_kebumi' => '2026-06-30', 'masa_kebumi' => '14:25', 'waris' => [['nama' => 'Latifah binti Yaakob', 'no_tel' => '010-4201005']]],
            ['nama_si_mati' => 'Mariam binti Yusof', 'no_ic' => '600104-08-4301', 'blok' => 'C', 'baris' => 8, 'lot' => 9, 'tarikh_kebumi' => '2026-06-12', 'masa_kebumi' => '08:45', 'waris' => [['nama' => 'Hafiz bin Yusof', 'no_tel' => '018-4301001']]],
            ['nama_si_mati' => 'Kassim bin Selamat', 'no_ic' => '450912-01-4302', 'blok' => 'C', 'baris' => 44, 'lot' => 2, 'tarikh_kebumi' => '2026-06-27', 'masa_kebumi' => '14:05', 'waris' => [['nama' => 'Sabariah binti Kassim', 'no_tel' => '019-4301002']]],
            ['nama_si_mati' => 'Salmah binti Ibrahim', 'no_ic' => '560312-10-4303', 'blok' => 'C', 'baris' => 14, 'lot' => 7, 'tarikh_kebumi' => '2026-06-23', 'masa_kebumi' => '09:05', 'waris' => [['nama' => 'Ridzuan bin Ibrahim', 'no_tel' => '011-4301003']]],
            ['nama_si_mati' => 'Hashim bin Ahmad', 'no_ic' => '480915-10-4304', 'blok' => 'C', 'baris' => 28, 'lot' => 10, 'tarikh_kebumi' => '2026-06-28', 'masa_kebumi' => '17:20', 'waris' => [['nama' => 'Siti Hajar binti Hashim', 'no_tel' => '012-4301004']]],
            ['nama_si_mati' => 'Khadijah binti Musa', 'no_ic' => '620625-10-4305', 'blok' => 'C', 'baris' => 56, 'lot' => 3, 'tarikh_kebumi' => '2026-07-01', 'masa_kebumi' => '10:30', 'waris' => [['nama' => 'Aiman bin Musa', 'no_tel' => '013-4301005']]],
        ];

        foreach ($records as $data) {
            $waris = $data['waris'];
            unset($data['waris']);

            $record = GraveRecord::updateOrCreate(
                ['no_ic' => $data['no_ic']],
                $data,
            );

            foreach ($waris as $warisData) {
                $record->waris()->updateOrCreate(
                    ['nama' => $warisData['nama']],
                    $warisData,
                );
            }
        }
    }
}
