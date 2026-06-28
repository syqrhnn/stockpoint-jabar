<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed initial data needed for tests
        DB::table('gudang')->insert([
            ['id' => 1, 'nama' => 'Gudang Pusat', 'lokasi' => 'Bandung', 'kapasitas' => 1000],
        ]);

        $users = [
            [
                'id' => 1,
                'nama' => 'Admin',
                'email' => 'admin@test.com',
                'password_hash' => Hash::make('password123'),
                'role' => 'admin_gudang',
                'gudang_id' => null,
                'is_active' => true,
            ],
            [
                'id' => 2,
                'nama' => 'Kepala',
                'email' => 'kepala@test.com',
                'password_hash' => Hash::make('password123'),
                'role' => 'kepala_gudang',
                'gudang_id' => 1,
                'is_active' => true,
            ],
            [
                'id' => 3,
                'nama' => 'Staf',
                'email' => 'staf@test.com',
                'password_hash' => Hash::make('password123'),
                'role' => 'staf_gudang',
                'gudang_id' => 1,
                'is_active' => true,
            ],
            [
                'id' => 4,
                'nama' => 'Manajer',
                'email' => 'manajer@test.com',
                'password_hash' => Hash::make('password123'),
                'role' => 'manajer_operasional',
                'gudang_id' => null,
                'is_active' => true,
            ],
            [
                'id' => 5,
                'nama' => 'Inactive',
                'email' => 'inactive@test.com',
                'password_hash' => Hash::make('password123'),
                'role' => 'staf_gudang',
                'gudang_id' => 1,
                'is_active' => false,
            ]
        ];

        DB::table('users')->insert($users);
    }

    public function test_login_success_admin()
    {
        $response = $this->post('/login', [
            'email' => 'admin@test.com',
            'password' => 'password123',
        ]);
        $response->assertRedirect('/admin/dashboard');
        $this->assertEquals('admin_gudang', session('role'));
    }

    public function test_login_success_kepala()
    {
        $response = $this->post('/login', [
            'email' => 'kepala@test.com',
            'password' => 'password123',
        ]);
        $response->assertRedirect('/kepala/dashboard');
        $this->assertEquals('kepala_gudang', session('role'));
    }

    public function test_login_success_staf()
    {
        $response = $this->post('/login', [
            'email' => 'staf@test.com',
            'password' => 'password123',
        ]);
        $response->assertRedirect('/staf/dashboard');
        $this->assertEquals('staf_gudang', session('role'));
    }

    public function test_login_success_manajer()
    {
        $response = $this->post('/login', [
            'email' => 'manajer@test.com',
            'password' => 'password123',
        ]);
        $response->assertRedirect('/manajer/dashboard');
        $this->assertEquals('manajer_operasional', session('role'));
    }

    public function test_login_failed_unregistered_email()
    {
        $response = $this->post('/login', [
            'email' => 'wrong@test.com',
            'password' => 'password123',
        ]);
        $response->assertSessionHasErrors('error');
        $this->assertNull(session('user_id'));
    }

    public function test_login_failed_wrong_password()
    {
        $response = $this->post('/login', [
            'email' => 'admin@test.com',
            'password' => 'wrongpassword',
        ]);
        $response->assertSessionHasErrors('error');
        $this->assertNull(session('user_id'));
    }

    public function test_login_failed_inactive_account()
    {
        $response = $this->post('/login', [
            'email' => 'inactive@test.com',
            'password' => 'password123',
        ]);
        $response->assertSessionHasErrors('error');
        $this->assertNull(session('user_id'));
    }

    public function test_unauthenticated_user_cannot_access_dashboard()
    {
        $response = $this->get('/admin/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_role_middleware_blocks_unauthorized_access_403()
    {
        // Login as staf
        $this->withSession(['user_id' => 3, 'role' => 'staf_gudang', 'gudang_id' => 1]);
        
        // Staf tries to access admin
        $response = $this->get('/admin/dashboard');
        $response->assertStatus(403);
    }

    public function test_logout_destroys_session()
    {
        $this->withSession(['user_id' => 1, 'role' => 'admin_gudang']);
        $response = $this->post('/logout');
        $response->assertRedirect('/login');
        $this->assertNull(session('user_id'));
    }
}
