<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        DB::table('gudang')->insert([
            'id' => 1,
            'nama' => 'Gudang Test',
            'lokasi' => 'Test',
            'kapasitas' => 100
        ]);

        DB::table('users')->insert([
            'id' => 1,
            'nama' => 'Admin',
            'email' => 'admin@test.com',
            'password_hash' => Hash::make('password123'),
            'role' => 'admin_gudang',
            'gudang_id' => null,
            'is_active' => true,
        ]);
        
        DB::table('users')->insert([
            'id' => 2,
            'nama' => 'Staf',
            'email' => 'staf@test.com',
            'password_hash' => Hash::make('password123'),
            'role' => 'staf_gudang',
            'gudang_id' => 1,
            'is_active' => true,
        ]);
    }

    public function test_user_crud_api_success()
    {
        $this->withSession(['user_id' => 1, 'role' => 'admin_gudang']);

        // Create
        $response = $this->postJson('/admin/api/pengguna', [
            'nama' => 'Budi Manajer',
            'email' => 'budi@test.com',
            'password' => 'password123',
            'role' => 'manajer_operasional',
            'gudang_id' => ''
        ]);
        $response->assertStatus(200);
        $userId = $response->json('data.id');

        // Create fails if gudang required
        $responseFails = $this->postJson('/admin/api/pengguna', [
            'nama' => 'Budi Staf',
            'email' => 'budistaf@test.com',
            'password' => 'password123',
            'role' => 'staf_gudang',
            'gudang_id' => ''
        ]);
        $responseFails->assertStatus(422);

        // Update
        $this->putJson('/admin/api/pengguna/'.$userId, [
            'nama' => 'Budi Manajer Updated',
            'email' => 'budi@test.com',
            'role' => 'manajer_operasional',
            'gudang_id' => ''
        ])->assertStatus(200);

        // Deactivate other user
        $this->patchJson('/admin/api/pengguna/'.$userId.'/deactivate')->assertStatus(200);
        
        // Deactivate self should fail
        $this->patchJson('/admin/api/pengguna/1/deactivate')->assertStatus(403);
    }
}
