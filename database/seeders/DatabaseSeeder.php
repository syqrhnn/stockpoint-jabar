<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\Services\StokService;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $now = now();
        $password = Hash::make('password123'); // Gunakan password standar untuk demo

        // 1. Seed Gudang
        $gudangs = [
            ['nama' => 'Gudang Pusat Bandung', 'lokasi' => 'Bandung', 'kapasitas' => 5000, 'created_at' => $now, 'updated_at' => $now],
            ['nama' => 'Gudang Cabang Bekasi', 'lokasi' => 'Bekasi', 'kapasitas' => 3000, 'created_at' => $now, 'updated_at' => $now],
            ['nama' => 'Gudang Cabang Bogor', 'lokasi' => 'Bogor', 'kapasitas' => 3500, 'created_at' => $now, 'updated_at' => $now],
        ];
        DB::table('gudang')->insert($gudangs);
        $gudangIds = DB::table('gudang')->pluck('id')->toArray();

        // 2. Seed Users (8 Users)
        $users = [
            // 1 Admin
            ['nama' => 'Super Admin', 'email' => 'admin@stockpoint.id', 'password_hash' => $password, 'role' => 'admin_gudang', 'gudang_id' => null, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            // 1 Manajer Operasional
            ['nama' => 'Manajer Operasional', 'email' => 'manajer@stockpoint.id', 'password_hash' => $password, 'role' => 'manajer_operasional', 'gudang_id' => null, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ];

        // 3 Kepala Gudang & 3 Staf Gudang (masing-masing 1 per gudang)
        for ($i = 0; $i < 3; $i++) {
            $gId = $gudangIds[$i];
            $users[] = ['nama' => "Kepala Gudang " . ($i+1), 'email' => "kepala{$i}@stockpoint.id", 'password_hash' => $password, 'role' => 'kepala_gudang', 'gudang_id' => $gId, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now];
            $users[] = ['nama' => "Staf Gudang " . ($i+1), 'email' => "staf{$i}@stockpoint.id", 'password_hash' => $password, 'role' => 'staf_gudang', 'gudang_id' => $gId, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now];
        }
        DB::table('users')->insert($users);

        // 3. Seed Supplier (3 Supplier)
        $suppliers = [
            ['nama' => 'PT Sumber Sembako', 'kontak' => '08111111111', 'lead_time_default' => 3, 'created_at' => $now, 'updated_at' => $now],
            ['nama' => 'CV Distribusi Pangan', 'kontak' => '08222222222', 'lead_time_default' => 4, 'created_at' => $now, 'updated_at' => $now],
            ['nama' => 'Bintang Grosir Jabar', 'kontak' => '08333333333', 'lead_time_default' => 5, 'created_at' => $now, 'updated_at' => $now],
        ];
        DB::table('supplier')->insert($suppliers);
        $supplierIds = DB::table('supplier')->pluck('id')->toArray();

        // 4. Seed Barang (10 Barang)
        $barangs = [
            ['nama' => 'Beras Premium 5kg', 'kategori' => 'Bahan Pokok', 'satuan' => 'Sak', 'created_at' => $now, 'updated_at' => $now],
            ['nama' => 'Beras Medium 5kg', 'kategori' => 'Bahan Pokok', 'satuan' => 'Sak', 'created_at' => $now, 'updated_at' => $now],
            ['nama' => 'Minyak Goreng 2L', 'kategori' => 'Bahan Pokok', 'satuan' => 'Pouch', 'created_at' => $now, 'updated_at' => $now],
            ['nama' => 'Gula Pasir 1kg', 'kategori' => 'Bahan Pokok', 'satuan' => 'Bungkus', 'created_at' => $now, 'updated_at' => $now],
            ['nama' => 'Tepung Terigu 1kg', 'kategori' => 'Bahan Pokok', 'satuan' => 'Bungkus', 'created_at' => $now, 'updated_at' => $now],
            ['nama' => 'Telur Ayam 1kg', 'kategori' => 'Bahan Pokok', 'satuan' => 'Tray', 'created_at' => $now, 'updated_at' => $now],
            ['nama' => 'Susu Kental Manis', 'kategori' => 'Minuman', 'satuan' => 'Kaleng', 'created_at' => $now, 'updated_at' => $now],
            ['nama' => 'Teh Celup Isi 25', 'kategori' => 'Minuman', 'satuan' => 'Kotak', 'created_at' => $now, 'updated_at' => $now],
            ['nama' => 'Kopi Bubuk 165g', 'kategori' => 'Minuman', 'satuan' => 'Bungkus', 'created_at' => $now, 'updated_at' => $now],
            ['nama' => 'Mie Instan Goreng', 'kategori' => 'Makanan Instan', 'satuan' => 'Kardus', 'created_at' => $now, 'updated_at' => $now],
        ];
        DB::table('barang')->insert($barangs);
        $barangIds = DB::table('barang')->pluck('id')->toArray();

        // 5. Setup Stok, ROP Parameter, dan 30 Hari Transaksi Keluar/Masuk
        // Gunakan StokService untuk keabsahan logika bisnis
        $notifService = new \App\Services\NotifikasiService();
        $stokService = new StokService($notifService);

        $admin = DB::table('users')->where('role', 'admin_gudang')->first();

        // Loop untuk setiap kombinasi
        foreach ($gudangIds as $gId) {
            foreach ($barangIds as $bId) {
                // 1. Initial Insert Stok
                DB::table('stok')->insert([
                    'barang_id' => $bId,
                    'gudang_id' => $gId,
                    'saldo' => 0,
                    'status' => 'belum_dikonfigurasi',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                // 2. Berikan Opening Balance via DB transaction simulasi
                $ob = rand(200, 500); // 200 - 500 unit
                $obDate = Carbon::now()->subDays(31)->toDateString();
                
                DB::table('transaksi_stok')->insert([
                    'barang_id' => $bId,
                    'gudang_id' => $gId,
                    'user_id' => $admin->id,
                    'jenis' => 'masuk',
                    'jumlah' => $ob,
                    'saldo_sebelum' => 0,
                    'saldo_sesudah' => $ob,
                    'tanggal' => $obDate,
                    'catatan' => 'Opening Balance - Simulasi',
                    'created_at' => $now,
                ]);
                DB::table('stok')->where('barang_id', $bId)->where('gudang_id', $gId)->update(['saldo' => $ob]);

                // 3. Simulasi Transaksi 30 Hari Terakhir (hanya keluar)
                $currentSaldo = $ob;
                for ($d = 30; $d >= 1; $d--) {
                    // Random peluang ada transaksi hari ini (70% probability)
                    if (rand(1, 100) <= 70) {
                        $qtyKeluar = rand(5, 15);
                        if ($currentSaldo >= $qtyKeluar) {
                            $tglTrans = Carbon::now()->subDays($d)->toDateString();
                            
                            DB::table('transaksi_stok')->insert([
                                'barang_id' => $bId,
                                'gudang_id' => $gId,
                                'user_id' => $admin->id,
                                'jenis' => 'keluar',
                                'jumlah' => $qtyKeluar,
                                'saldo_sebelum' => $currentSaldo,
                                'saldo_sesudah' => $currentSaldo - $qtyKeluar,
                                'tanggal' => $tglTrans,
                                'catatan' => 'Penjualan / Distribusi Harian',
                                'created_at' => $now,
                            ]);
                            $currentSaldo -= $qtyKeluar;
                        }
                    }
                }
                
                DB::table('stok')->where('barang_id', $bId)->where('gudang_id', $gId)->update(['saldo' => $currentSaldo]);

                // 4. Konfigurasi ROP Parameter
                // ADU = total keluar selama 30 hari / 30
                $stokService->simpanParameterRop([
                    'barang_id' => $bId,
                    'gudang_id' => $gId,
                    'lead_time' => rand(3, 7), // 3-7 hari
                    'safety_stock' => rand(20, 50),
                ], $admin);
            }
        }
    }
}
