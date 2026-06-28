<?php

namespace Database\Seeders;

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
        // Seed Gudang
        \Illuminate\Support\Facades\DB::table('gudang')->insert([
            [
                'nama' => 'Gudang Kota Bandung',
                'lokasi' => 'Kota Bandung',
                'kapasitas' => 1000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Gudang Kabupaten Bekasi',
                'lokasi' => 'Kabupaten Bekasi',
                'kapasitas' => 2000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Gudang Kota Bogor',
                'lokasi' => 'Kota Bogor',
                'kapasitas' => 1500,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Seed User Admin
        \Illuminate\Support\Facades\DB::table('users')->insert([
            'nama' => 'Admin Gudang',
            'email' => 'admin@stockpoint.id',
            'password_hash' => \Illuminate\Support\Facades\Hash::make('Admin123!'),
            'role' => 'admin_gudang',
            'gudang_id' => null,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
