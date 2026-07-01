<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\GraveRecord;
use App\Models\GraveWaris;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
