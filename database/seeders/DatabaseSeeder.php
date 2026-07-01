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
        User::firstOrCreate(
            ['email' => 'admin@mykubur.com'],
            ['name' => 'Pengurus Kubur (Admin)', 'password' => 'admin123', 'role' => 'admin']
        );

        User::firstOrCreate(
            ['email' => 'waris@mykubur.com'],
            ['name' => 'Mohd Waris', 'password' => 'waris123', 'role' => 'waris']
        );

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
            [
                'nama_si_mati' => 'Halimah binti Hassan',
                'no_ic' => '550615-05-3344',
                'blok' => 'A',
                'baris' => 2,
                'lot' => 3,
                'tarikh_kebumi' => '2026-01-20',
                'masa_kebumi' => '08:15',
                'waris' => [
                    ['nama' => 'Rahman bin Hassan', 'no_tel' => '014-5556666'],
                ],
            ],
            [
                'nama_si_mati' => 'Noraini binti Zainal',
                'no_ic' => '620720-10-5577',
                'blok' => 'B',
                'baris' => 3,
                'lot' => 5,
                'tarikh_kebumi' => '2026-02-05',
                'masa_kebumi' => '13:45',
                'waris' => [
                    ['nama' => 'Zainal bin Zainal', 'no_tel' => '015-6667777'],
                ],
            ],
            [
                'nama_si_mati' => 'Ismail bin Yusof',
                'no_ic' => '480304-01-2211',
                'blok' => 'C',
                'baris' => 4,
                'lot' => 8,
                'tarikh_kebumi' => '2026-03-10',
                'masa_kebumi' => '10:00',
                'waris' => [
                    ['nama' => 'Aisha binti Ismail', 'no_tel' => '016-7778888'],
                    ['nama' => 'Yusof bin Ismail', 'no_tel' => '010-1112222'],
                ],
            ],
            [
                'nama_si_mati' => 'Zainab binti Ahmad',
                'no_ic' => '580225-07-4488',
                'blok' => 'D',
                'baris' => 1,
                'lot' => 2,
                'tarikh_kebumi' => '2026-01-30',
                'masa_kebumi' => '09:30',
                'waris' => [
                    ['nama' => 'Ahmad bin Ahmad', 'no_tel' => '012-3334444'],
                ],
            ],
            [
                'nama_si_mati' => 'Jamaluddin bin Hamid',
                'no_ic' => '470812-03-6655',
                'blok' => 'A',
                'baris' => 3,
                'lot' => 4,
                'tarikh_kebumi' => '2026-02-14',
                'masa_kebumi' => '11:20',
                'waris' => [
                    ['nama' => 'Hamid bin Jamaluddin', 'no_tel' => '013-5556666'],
                ],
            ],
            [
                'nama_si_mati' => 'Mahani binti Rahman',
                'no_ic' => '610508-02-7799',
                'blok' => 'B',
                'baris' => 4,
                'lot' => 6,
                'tarikh_kebumi' => '2026-03-15',
                'masa_kebumi' => '14:50',
                'waris' => [
                    ['nama' => 'Rahman bin Rahman', 'no_tel' => '014-6667777'],
                ],
            ],
            [
                'nama_si_mati' => 'Kamaruddin bin Omar',
                'no_ic' => '540927-08-3322',
                'blok' => 'C',
                'baris' => 5,
                'lot' => 9,
                'tarikh_kebumi' => '2026-01-25',
                'masa_kebumi' => '10:45',
                'waris' => [
                    ['nama' => 'Omar bin Kamaruddin', 'no_tel' => '015-8889999'],
                ],
            ],
            [
                'nama_si_mati' => 'Rosmah binti Ibrahim',
                'no_ic' => '670106-10-5533',
                'blok' => 'D',
                'baris' => 2,
                'lot' => 3,
                'tarikh_kebumi' => '2026-02-28',
                'masa_kebumi' => '12:15',
                'waris' => [
                    ['nama' => 'Ibrahim bin Ibrahim', 'no_tel' => '016-1111222'],
                    ['nama' => 'Siti binti Ibrahim', 'no_tel' => '010-2223333'],
                ],
            ],
            [
                'nama_si_mati' => 'Mustafa bin Nasir',
                'no_ic' => '500710-05-4466',
                'blok' => 'A',
                'baris' => 4,
                'lot' => 5,
                'tarikh_kebumi' => '2026-03-08',
                'masa_kebumi' => '08:30',
                'waris' => [
                    ['nama' => 'Nasir bin Mustafa', 'no_tel' => '012-4445555'],
                ],
            ],
            [
                'nama_si_mati' => 'Azizah binti Karim',
                'no_ic' => '580419-07-6688',
                'blok' => 'B',
                'baris' => 6,
                'lot' => 7,
                'tarikh_kebumi' => '2026-02-10',
                'masa_kebumi' => '15:30',
                'waris' => [
                    ['nama' => 'Karim bin Karim', 'no_tel' => '013-7778888'],
                ],
            ],
            [
                'nama_si_mati' => 'Salina binti Salim',
                'no_ic' => '640623-04-2299',
                'blok' => 'C',
                'baris' => 6,
                'lot' => 10,
                'tarikh_kebumi' => '2026-03-22',
                'masa_kebumi' => '11:00',
                'waris' => [
                    ['nama' => 'Salim bin Salim', 'no_tel' => '014-9999000'],
                ],
            ],
            [
                'nama_si_mati' => 'Fauzi bin Farah',
                'no_ic' => '520911-01-8811',
                'blok' => 'D',
                'baris' => 3,
                'lot' => 4,
                'tarikh_kebumi' => '2026-01-18',
                'masa_kebumi' => '09:45',
                'waris' => [
                    ['nama' => 'Farah binti Fauzi', 'no_tel' => '015-1112222'],
                ],
            ],
            [
                'nama_si_mati' => 'Habibah binti Hamid',
                'no_ic' => '690305-06-9922',
                'blok' => 'A',
                'baris' => 5,
                'lot' => 6,
                'tarikh_kebumi' => '2026-02-22',
                'masa_kebumi' => '13:20',
                'waris' => [
                    ['nama' => 'Hamid bin Hamid', 'no_tel' => '016-2223333'],
                    ['nama' => 'Nurdin bin Hamid', 'no_tel' => '010-3334444'],
                ],
            ],
            [
                'nama_si_mati' => 'Rizal bin Rais',
                'no_ic' => '550814-02-7744',
                'blok' => 'B',
                'baris' => 7,
                'lot' => 8,
                'tarikh_kebumi' => '2026-03-05',
                'masa_kebumi' => '10:30',
                'waris' => [
                    ['nama' => 'Rais bin Rizal', 'no_tel' => '012-5556666'],
                ],
            ],
            [
                'nama_si_mati' => 'Syarifah binti Zainal',
                'no_ic' => '610729-08-6633',
                'blok' => 'C',
                'baris' => 7,
                'lot' => 11,
                'tarikh_kebumi' => '2026-02-16',
                'masa_kebumi' => '14:15',
                'waris' => [
                    ['nama' => 'Zainal bin Zainal', 'no_tel' => '013-6667777'],
                ],
            ],
            [
                'nama_si_mati' => 'Tariq bin Tarik',
                'no_ic' => '470503-10-4455',
                'blok' => 'D',
                'baris' => 4,
                'lot' => 5,
                'tarikh_kebumi' => '2026-01-28',
                'masa_kebumi' => '11:50',
                'waris' => [
                    ['nama' => 'Tarik bin Tariq', 'no_tel' => '014-8889999'],
                ],
            ],
            [
                'nama_si_mati' => 'Ummah binti Usman',
                'no_ic' => '680211-03-5544',
                'blok' => 'A',
                'baris' => 6,
                'lot' => 7,
                'tarikh_kebumi' => '2026-03-12',
                'masa_kebumi' => '09:15',
                'waris' => [
                    ['nama' => 'Usman bin Usman', 'no_tel' => '015-1112222'],
                ],
            ],
            [
                'nama_si_mati' => 'Wahida binti Wahab',
                'no_ic' => '550426-05-8866',
                'blok' => 'B',
                'baris' => 8,
                'lot' => 9,
                'tarikh_kebumi' => '2026-02-03',
                'masa_kebumi' => '12:40',
                'waris' => [
                    ['nama' => 'Wahab bin Wahab', 'no_tel' => '016-3334444'],
                ],
            ],
            [
                'nama_si_mati' => 'Yusri bin Yahya',
                'no_ic' => '620614-07-9977',
                'blok' => 'C',
                'baris' => 8,
                'lot' => 12,
                'tarikh_kebumi' => '2026-03-20',
                'masa_kebumi' => '15:05',
                'waris' => [
                    ['nama' => 'Yahya bin Yusri', 'no_tel' => '010-4445555'],
                    ['nama' => 'Zahra binti Yusri', 'no_tel' => '012-6667777'],
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
