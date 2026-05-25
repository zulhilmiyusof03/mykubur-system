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
            ['nama_si_mati' => 'Hassan bin Omar', 'no_ic' => '500101-01-1001', 'blok' => 'A', 'baris' => 2, 'lot' => 1, 'tarikh_kebumi' => '2025-01-08', 'masa_kebumi' => '09:30', 'waris' => [['nama' => 'Noraini binti Hassan', 'no_tel' => '012-1001001']]],
            ['nama_si_mati' => 'Ramlah binti Salleh', 'no_ic' => '520214-02-1002', 'blok' => 'A', 'baris' => 2, 'lot' => 2, 'tarikh_kebumi' => '2025-01-22', 'masa_kebumi' => '10:15', 'waris' => [['nama' => 'Azman bin Ibrahim', 'no_tel' => '012-1001002']]],
            ['nama_si_mati' => 'Ismail bin Latif', 'no_ic' => '470603-03-1003', 'blok' => 'A', 'baris' => 2, 'lot' => 3, 'tarikh_kebumi' => '2025-02-05', 'masa_kebumi' => '11:00', 'waris' => [['nama' => 'Maznah binti Ismail', 'no_tel' => '012-1001003']]],
            ['nama_si_mati' => 'Zaiton binti Hamid', 'no_ic' => '560721-04-1004', 'blok' => 'A', 'baris' => 3, 'lot' => 1, 'tarikh_kebumi' => '2025-02-18', 'masa_kebumi' => '14:30', 'waris' => [['nama' => 'Farid bin Musa', 'no_tel' => '012-1001004']]],
            ['nama_si_mati' => 'Mahmud bin Rahman', 'no_ic' => '430911-05-1005', 'blok' => 'A', 'baris' => 3, 'lot' => 2, 'tarikh_kebumi' => '2025-03-03', 'masa_kebumi' => '09:45', 'waris' => [['nama' => 'Salmah binti Mahmud', 'no_tel' => '012-1001005']]],
            ['nama_si_mati' => 'Khadijah binti Nordin', 'no_ic' => '601204-06-1006', 'blok' => 'B', 'baris' => 1, 'lot' => 1, 'tarikh_kebumi' => '2025-03-19', 'masa_kebumi' => '10:00', 'waris' => [['nama' => 'Hafiz bin Nordin', 'no_tel' => '012-1001006']]],
            ['nama_si_mati' => 'Yusof bin Daud', 'no_ic' => '490815-07-1007', 'blok' => 'B', 'baris' => 1, 'lot' => 2, 'tarikh_kebumi' => '2025-04-02', 'masa_kebumi' => '15:15', 'waris' => [['nama' => 'Mariam binti Yusof', 'no_tel' => '012-1001007']]],
            ['nama_si_mati' => 'Sofiah binti Karim', 'no_ic' => '580926-08-1008', 'blok' => 'B', 'baris' => 1, 'lot' => 3, 'tarikh_kebumi' => '2025-04-21', 'masa_kebumi' => '08:45', 'waris' => [['nama' => 'Ridzuan bin Karim', 'no_tel' => '012-1001008']]],
            ['nama_si_mati' => 'Jamaluddin bin Ahmad', 'no_ic' => '460110-09-1009', 'blok' => 'B', 'baris' => 2, 'lot' => 1, 'tarikh_kebumi' => '2025-05-06', 'masa_kebumi' => '12:00', 'waris' => [['nama' => 'Rohana binti Jamaluddin', 'no_tel' => '012-1001009']]],
            ['nama_si_mati' => 'Hasnah binti Osman', 'no_ic' => '630322-10-1010', 'blok' => 'B', 'baris' => 2, 'lot' => 2, 'tarikh_kebumi' => '2025-05-27', 'masa_kebumi' => '10:40', 'waris' => [['nama' => 'Syafiq bin Osman', 'no_tel' => '012-1001010']]],
            ['nama_si_mati' => 'Razak bin Wahab', 'no_ic' => '511231-11-1011', 'blok' => 'B', 'baris' => 2, 'lot' => 3, 'tarikh_kebumi' => '2025-06-09', 'masa_kebumi' => '16:10', 'waris' => [['nama' => 'Nabilah binti Razak', 'no_tel' => '012-1001011']]],
            ['nama_si_mati' => 'Aishah binti Harun', 'no_ic' => '570412-12-1012', 'blok' => 'C', 'baris' => 1, 'lot' => 1, 'tarikh_kebumi' => '2025-06-25', 'masa_kebumi' => '09:20', 'waris' => [['nama' => 'Faizal bin Harun', 'no_tel' => '012-1001012']]],
            ['nama_si_mati' => 'Bakar bin Mansor', 'no_ic' => '441007-01-1013', 'blok' => 'C', 'baris' => 1, 'lot' => 2, 'tarikh_kebumi' => '2025-07-11', 'masa_kebumi' => '11:30', 'waris' => [['nama' => 'Sarina binti Bakar', 'no_tel' => '012-1001013']]],
            ['nama_si_mati' => 'Rokiah binti Mat', 'no_ic' => '620518-02-1014', 'blok' => 'C', 'baris' => 3, 'lot' => 3, 'tarikh_kebumi' => '2025-07-28', 'masa_kebumi' => '13:45', 'waris' => [['nama' => 'Hakim bin Mat', 'no_tel' => '012-1001014']]],
            ['nama_si_mati' => 'Sulaiman bin Hashim', 'no_ic' => '530909-03-1015', 'blok' => 'C', 'baris' => 2, 'lot' => 1, 'tarikh_kebumi' => '2025-08-13', 'masa_kebumi' => '10:05', 'waris' => [['nama' => 'Latifah binti Sulaiman', 'no_tel' => '012-1001015']]],
            ['nama_si_mati' => 'Maimunah binti Ghani', 'no_ic' => '590201-04-1016', 'blok' => 'C', 'baris' => 2, 'lot' => 2, 'tarikh_kebumi' => '2025-08-29', 'masa_kebumi' => '09:10', 'waris' => [['nama' => 'Fauzi bin Ghani', 'no_tel' => '012-1001016']]],
            ['nama_si_mati' => 'Zulkifli bin Basri', 'no_ic' => '480630-05-1017', 'blok' => 'C', 'baris' => 2, 'lot' => 3, 'tarikh_kebumi' => '2025-09-14', 'masa_kebumi' => '15:30', 'waris' => [['nama' => 'Halimah binti Zulkifli', 'no_tel' => '012-1001017']]],
            ['nama_si_mati' => 'Noriah binti Saad', 'no_ic' => '640817-06-1018', 'blok' => 'A', 'baris' => 4, 'lot' => 1, 'tarikh_kebumi' => '2025-10-01', 'masa_kebumi' => '08:30', 'waris' => [['nama' => 'Amirul bin Saad', 'no_tel' => '012-1001018']]],
            ['nama_si_mati' => 'Hamzah bin Jalil', 'no_ic' => '550505-07-1019', 'blok' => 'A', 'baris' => 4, 'lot' => 2, 'tarikh_kebumi' => '2025-10-17', 'masa_kebumi' => '14:00', 'waris' => [['nama' => 'Suraya binti Hamzah', 'no_tel' => '012-1001019']]],
            ['nama_si_mati' => 'Fatimah binti Rahim', 'no_ic' => '610728-08-1020', 'blok' => 'A', 'baris' => 4, 'lot' => 3, 'tarikh_kebumi' => '2025-11-04', 'masa_kebumi' => '10:50', 'waris' => [['nama' => 'Ikhwan bin Rahim', 'no_tel' => '012-1001020']]],
        ];

        DB::transaction(function () use ($records) {
            foreach ($records as $data) {
                $waris = $data['waris'];
                unset($data['waris']);

                $record = GraveRecord::updateOrCreate(
                    ['no_ic' => $data['no_ic']],
                    $data
                );

                $record->waris()->delete();
                $record->waris()->createMany($waris);
            }
        });
    }
}
