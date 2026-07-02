<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\GraveRecord;
use App\Models\GraveWaris;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class GraveRecordTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_and_login()
    {
        // 1. Test Register
        $registerResponse = $this->postJson('/auth/register', [
            'name' => 'Waris Baru',
            'email' => 'warisbaru@mykubur.com',
            'password' => 'password123',
        ]);

        $registerResponse->assertStatus(201)
            ->assertJsonPath('user.name', 'Waris Baru')
            ->assertJsonPath('user.role', 'waris');

        $this->assertDatabaseHas('users', [
            'email' => 'warisbaru@mykubur.com',
            'role' => 'waris',
        ]);

        // 2. Test Login
        $loginResponse = $this->postJson('/auth/login', [
            'email' => 'warisbaru@mykubur.com',
            'password' => 'password123',
        ]);

        $loginResponse->assertStatus(200)
            ->assertJsonPath('user.name', 'Waris Baru');
    }

    public function test_login_shows_clear_message_for_unregistered_account()
    {
        $this->postJson('/auth/login', [
            'email' => 'belumdaftar@mykubur.com',
            'password' => 'password123',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('email')
            ->assertJsonPath('errors.email.0', 'Akaun ini tidak berdaftar. Sila daftar akaun waris terlebih dahulu.');
    }

    public function test_login_shows_clear_message_for_wrong_password()
    {
        User::factory()->create([
            'email' => 'waris-test@mykubur.com',
            'password' => 'password123',
            'role' => 'waris',
        ]);

        $this->postJson('/auth/login', [
            'email' => 'waris-test@mykubur.com',
            'password' => 'salah123',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('password')
            ->assertJsonPath('errors.password.0', 'Kata laluan salah. Sila cuba semula');
    }

    public function test_can_create_grave_record_with_waris_relationship()
    {
        $payload = [
            'nama_si_mati' => 'Arwah Ahmad',
            'no_ic' => '700101-01-9999',
            'blok' => 'B',
            'baris' => 12,
            'lot' => 5,
            'tarikh_kebumi' => '2026-03-15',
            'masa_kebumi' => '14:30',
            'waris' => [
                ['nama' => 'Waris Pertama', 'no_tel' => '012-3456789'],
                ['nama' => 'Waris Kedua', 'no_tel' => '019-8765432'],
            ],
        ];

        $response = $this->postJson('/grave-records', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('nama_si_mati', 'Arwah Ahmad');

        // Check GraveRecord
        $this->assertDatabaseHas('grave_records', [
            'nama_si_mati' => 'Arwah Ahmad',
            'no_ic' => '700101-01-9999',
            'blok' => 'B',
            'baris' => 12,
            'lot' => 5,
        ]);

        // Get created record
        $record = GraveRecord::where('no_ic', '700101-01-9999')->first();
        $this->assertNotNull($record);

        // Check Relationship
        $this->assertCount(2, $record->waris);
        $this->assertDatabaseHas('grave_waris', [
            'grave_record_id' => $record->id,
            'nama' => 'Waris Pertama',
            'no_tel' => '012-3456789',
        ]);
        $this->assertDatabaseHas('grave_waris', [
            'grave_record_id' => $record->id,
            'nama' => 'Waris Kedua',
            'no_tel' => '019-8765432',
        ]);
    }

    public function test_admin_can_add_lot_beyond_default_row_capacity()
    {
        $payload = [
            'nama_si_mati' => 'Arwah Lot Tambahan',
            'no_ic' => '700101-01-8888',
            'blok' => 'B',
            'baris' => 6,
            'lot' => 6,
            'tarikh_kebumi' => '2026-06-15',
            'masa_kebumi' => '14:30',
            'waris' => [
                ['nama' => 'Waris Semakan', 'no_tel' => '012-3456789'],
            ],
        ];

        $response = $this->postJson('/grave-records', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('blok', 'B')
            ->assertJsonPath('baris', 6)
            ->assertJsonPath('lot', 6);

        $this->assertDatabaseHas('grave_records', [
            'blok' => 'B',
            'baris' => 6,
            'lot' => 6,
        ]);
    }

    public function test_duplicate_lot_in_same_row_is_rejected()
    {
        $payload = [
            'nama_si_mati' => 'Arwah Pertama',
            'no_ic' => '700101-01-6666',
            'blok' => 'A',
            'baris' => 1,
            'lot' => 11,
            'tarikh_kebumi' => '2026-06-15',
            'masa_kebumi' => '14:30',
            'waris' => [
                ['nama' => 'Waris Pertama', 'no_tel' => '012-3456789'],
            ],
        ];

        $this->postJson('/grave-records', $payload)->assertStatus(201);

        $duplicate = [
            ...$payload,
            'nama_si_mati' => 'Arwah Kedua',
            'no_ic' => '700101-01-5555',
        ];

        $response = $this->postJson('/grave-records', $duplicate);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('lot');
    }

    public function test_rows_cannot_exceed_fifty_seven()
    {
        $payload = [
            'nama_si_mati' => 'Arwah Luar Julat',
            'no_ic' => '700101-01-7777',
            'blok' => 'A',
            'baris' => 58,
            'lot' => 1,
            'tarikh_kebumi' => '2026-06-15',
            'masa_kebumi' => '14:30',
            'waris' => [
                ['nama' => 'Waris Luar Julat', 'no_tel' => '012-3456789'],
            ],
        ];

        $response = $this->postJson('/grave-records', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('baris');
    }

    public function test_admin_can_add_rows_and_capacity_updates()
    {
        $this->postJson('/grave-records/rows', [
            'blok' => 'A',
            'rows_to_add' => 2,
        ])
            ->assertStatus(200)
            ->assertJsonPath('block', 'A')
            ->assertJsonPath('row_count', 59)
            ->assertJsonPath('capacities.A.row_count', 59)
            ->assertJsonPath('capacities.A.total_capacity', 590);

        $payload = [
            'nama_si_mati' => 'Arwah Baris Tambahan',
            'no_ic' => '700101-01-3333',
            'blok' => 'A',
            'baris' => 59,
            'lot' => 10,
            'tarikh_kebumi' => '2026-06-15',
            'masa_kebumi' => '14:30',
            'waris' => [
                ['nama' => 'Waris Baris Tambahan', 'no_tel' => '012-3456789'],
            ],
        ];

        $this->postJson('/grave-records', $payload)
            ->assertStatus(201)
            ->assertJsonPath('blok', 'A')
            ->assertJsonPath('baris', 59)
            ->assertJsonPath('lot', 10);

        $this->getJson('/grave-records/capacities')
            ->assertStatus(200)
            ->assertJsonPath('A.row_count', 59)
            ->assertJsonPath('A.total_capacity', 590);
    }

    public function test_capacity_endpoints_recover_when_block_layout_table_is_missing()
    {
        Schema::dropIfExists('grave_block_layouts');

        $this->getJson('/grave-records/capacities')
            ->assertStatus(200)
            ->assertJsonPath('A.row_count', 57)
            ->assertJsonPath('A.total_capacity', 570);

        $this->postJson('/grave-records/rows', [
            'blok' => 'C',
            'rows_to_add' => 1,
        ])
            ->assertStatus(200)
            ->assertJsonPath('block', 'C')
            ->assertJsonPath('row_count', 58)
            ->assertJsonPath('capacities.C.total_capacity', 580);

        $this->assertDatabaseHas('grave_block_layouts', [
            'blok' => 'C',
            'row_count' => 58,
        ]);
    }

    public function test_can_search_grave_record_by_deceased_ic_or_waris_name()
    {
        $record = GraveRecord::create([
            'nama_si_mati' => 'Arwah Carian',
            'no_ic' => '700101-01-1234',
            'blok' => 'A',
            'baris' => 1,
            'lot' => 1,
            'tarikh_kebumi' => '2026-06-15',
            'masa_kebumi' => '14:30',
        ]);

        $record->waris()->create([
            'nama' => 'Noraini binti Semakan',
            'no_tel' => '012-3456789',
        ]);

        $this->getJson('/grave-records/search?q=Noraini')
            ->assertStatus(200)
            ->assertJsonPath('0.nama_si_mati', 'Arwah Carian')
            ->assertJsonPath('0.no_ic', '700101-01-1234')
            ->assertJsonPath('0.blok', 'A')
            ->assertJsonPath('0.nombor_lot', 'A1-1')
            ->assertJsonPath('0.lokasi_zon', 'Zon Kanan')
            ->assertJsonPath('0.nama_waris.0', 'Noraini binti Semakan');

        $this->getJson('/grave-records/search?q=700101')
            ->assertStatus(200)
            ->assertJsonPath('0.nama_si_mati', 'Arwah Carian');
    }

    public function test_block_capacity_updates_from_actual_slots_after_create_and_delete()
    {
        $this->getJson('/grave-records/capacities')
            ->assertStatus(200)
            ->assertJsonPath('A.total_capacity', 570);

        $payload = [
            'nama_si_mati' => 'Arwah Kapasiti Tambahan',
            'no_ic' => '700101-01-4444',
            'blok' => 'A',
            'baris' => 1,
            'lot' => 11,
            'tarikh_kebumi' => '2026-06-15',
            'masa_kebumi' => '14:30',
            'waris' => [
                ['nama' => 'Waris Kapasiti', 'no_tel' => '012-3456789'],
            ],
        ];

        $created = $this->postJson('/grave-records', $payload)
            ->assertStatus(201)
            ->json();

        $this->getJson('/grave-records/capacities')
            ->assertStatus(200)
            ->assertJsonPath('A.total_capacity', 571);

        $this->deleteJson("/grave-records/{$created['id']}")
            ->assertStatus(200);

        $this->getJson('/grave-records/capacities')
            ->assertStatus(200)
            ->assertJsonPath('A.total_capacity', 570);
    }
}
