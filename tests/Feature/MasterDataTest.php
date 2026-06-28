<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MasterDataTest extends TestCase
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
            'password_hash' => bcrypt('password123'),
            'role' => 'admin_gudang',
            'gudang_id' => null,
            'is_active' => true,
        ]);
        
        DB::table('users')->insert([
            'id' => 2,
            'nama' => 'Staf',
            'email' => 'staf@test.com',
            'password_hash' => bcrypt('password123'),
            'role' => 'staf_gudang',
            'gudang_id' => 1,
            'is_active' => true,
        ]);
    }

    public function test_barang_crud_api_success_as_admin()
    {
        // Simulate login as admin
        $this->withSession(['user_id' => 1, 'role' => 'admin_gudang']);

        // Create
        $response = $this->postJson('/admin/api/barang', [
            'nama' => 'Beras Pandan Wangi',
            'kategori' => 'Bahan Pokok',
            'satuan' => 'kg'
        ]);
        $response->assertStatus(200)->assertJson(['success' => true]);
        
        $barangId = $response->json('data.id');

        // Read
        $response = $this->getJson('/admin/api/barang');
        $response->assertStatus(200)->assertJsonPath('data.data.0.nama', 'Beras Pandan Wangi');

        // Update
        $response = $this->putJson('/admin/api/barang/'.$barangId, [
            'nama' => 'Beras Ramos',
            'kategori' => 'Bahan Pokok',
            'satuan' => 'kg'
        ]);
        $response->assertStatus(200)->assertJsonPath('data.nama', 'Beras Ramos');

        // Delete
        $response = $this->deleteJson('/admin/api/barang/'.$barangId);
        $response->assertStatus(200)->assertJson(['success' => true]);

        $this->assertDatabaseMissing('barang', ['id' => $barangId]);
    }

    public function test_barang_validation_fails()
    {
        $this->withSession(['user_id' => 1, 'role' => 'admin_gudang']);

        // Missing required fields
        $response = $this->postJson('/admin/api/barang', []);
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['nama', 'kategori', 'satuan']);
    }

    public function test_gudang_crud_api_success()
    {
        $this->withSession(['user_id' => 1, 'role' => 'admin_gudang']);

        // Create
        $response = $this->postJson('/admin/api/gudang', [
            'nama' => 'Gudang Pusat',
            'lokasi' => 'Bandung',
            'kapasitas' => 1000
        ]);
        $response->assertStatus(200);
        $gudangId = $response->json('data.id');

        // Delete
        $this->deleteJson('/admin/api/gudang/'.$gudangId)->assertStatus(200);
    }

    public function test_supplier_crud_api_success()
    {
        $this->withSession(['user_id' => 1, 'role' => 'admin_gudang']);

        // Create
        $response = $this->postJson('/admin/api/supplier', [
            'nama' => 'PT Suplai Nusantara',
            'kontak' => '08123456789',
            'lead_time_default' => 3
        ]);
        $response->assertStatus(200);
        $supplierId = $response->json('data.id');

        // Delete
        $this->deleteJson('/admin/api/supplier/'.$supplierId)->assertStatus(200);
    }

    public function test_unauthorized_access_by_staf_fails()
    {
        $this->withSession(['user_id' => 2, 'role' => 'staf_gudang']);

        // Staf should be forbidden from accessing admin API
        $response = $this->getJson('/admin/api/barang');
        $response->assertStatus(403);
    }
}
