<?php

namespace Tests\Feature;

use App\Services\NotifikasiService;
use App\Services\StokService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Exception;

class StokServiceTest extends TestCase
{
    use RefreshDatabase;

    protected StokService $stokService;
    protected $adminUser;
    protected $stafUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->stokService = new StokService(new NotifikasiService());

        // Setup master data
        DB::table('gudang')->insert([
            ['id' => 1, 'nama' => 'Gudang Pusat', 'lokasi' => 'Bandung', 'kapasitas' => 1000],
            ['id' => 2, 'nama' => 'Gudang Cabang', 'lokasi' => 'Jakarta', 'kapasitas' => 500]
        ]);

        DB::table('barang')->insert([
            'id' => 1, 'nama' => 'Barang A', 'kategori' => 'Kat A', 'satuan' => 'pcs'
        ]);

        DB::table('users')->insert([
            ['id' => 1, 'nama' => 'Admin', 'email' => 'admin@t.com', 'password_hash' => 'x', 'role' => 'admin_gudang', 'gudang_id' => null, 'is_active' => true],
            ['id' => 2, 'nama' => 'Staf', 'email' => 'staf@t.com', 'password_hash' => 'y', 'role' => 'staf_gudang', 'gudang_id' => 1, 'is_active' => true]
        ]);

        $this->adminUser = (object) ['id' => 1, 'role' => 'admin_gudang', 'gudang_id' => null];
        $this->stafUser = (object) ['id' => 2, 'role' => 'staf_gudang', 'gudang_id' => 1];
    }

    public function test_stok_masuk_berhasil()
    {
        $data = [
            'barang_id' => 1,
            'gudang_id' => 1,
            'jumlah' => 100,
            'tanggal' => now()->toDateString(),
            'catatan' => 'Restock awal'
        ];

        $transaksi = $this->stokService->catatStokMasuk($data, $this->stafUser);

        $this->assertEquals('masuk', $transaksi->jenis);
        $this->assertEquals(100, $transaksi->saldo_sesudah);

        $stok = DB::table('stok')->where('barang_id', 1)->where('gudang_id', 1)->first();
        $this->assertEquals(100, $stok->saldo);
    }

    public function test_staf_tidak_bisa_input_gudang_lain()
    {
        $this->expectException(Exception::class);
        
        $data = [
            'barang_id' => 1,
            'gudang_id' => 2, // Staf ini di gudang 1
            'jumlah' => 100,
            'tanggal' => now()->toDateString()
        ];

        $this->stokService->catatStokMasuk($data, $this->stafUser);
    }

    public function test_stok_keluar_melebihi_saldo_gagal()
    {
        // Masuk 50
        $this->stokService->catatStokMasuk([
            'barang_id' => 1, 'gudang_id' => 1, 'jumlah' => 50, 'tanggal' => now()->toDateString()
        ], $this->adminUser);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Stok tidak mencukupi");

        // Keluar 60 (harus gagal)
        $this->stokService->catatStokKeluar([
            'barang_id' => 1, 'gudang_id' => 1, 'jumlah' => 60, 'tanggal' => now()->toDateString()
        ], $this->adminUser);
    }

    public function test_hitung_adu_dan_rop()
    {
        // Masuk 100
        $this->stokService->catatStokMasuk([
            'barang_id' => 1, 'gudang_id' => 1, 'jumlah' => 100, 'tanggal' => now()->toDateString()
        ], $this->adminUser);

        // Keluar 30 (hari ini)
        $this->stokService->catatStokKeluar([
            'barang_id' => 1, 'gudang_id' => 1, 'jumlah' => 30, 'tanggal' => now()->toDateString()
        ], $this->adminUser);

        // ADU = 30 / 30 = 1
        $adu = $this->stokService->hitungADU(1, 1);
        $this->assertEquals(1.0, $adu);

        // ROP parameter
        DB::table('rop_parameter')->insert([
            'barang_id' => 1, 'gudang_id' => 1, 'lead_time' => 5, 'safety_stock' => 10
        ]);

        // ROP = (1 * 5) + 10 = 15
        $rop = $this->stokService->hitungROP(1, 1);
        $this->assertEquals(15.0, $rop);

        // Saldo sekarang: 100 - 30 = 70. 70 > 15 -> 'aman'
        $status = $this->stokService->evaluasiStatusStok(1, 1);
        $this->assertEquals('aman', $status);
    }

    public function test_stock_adjustment_simpan_log_audit()
    {
        $this->stokService->catatStokMasuk([
            'barang_id' => 1, 'gudang_id' => 1, 'jumlah' => 50, 'tanggal' => now()->toDateString()
        ], $this->adminUser);

        $this->stokService->stockAdjustment([
            'barang_id' => 1, 'gudang_id' => 1, 'jumlah_koreksi' => -10, 'catatan' => 'Barang rusak'
        ], $this->adminUser);

        $stok = DB::table('stok')->where('barang_id', 1)->first();
        $this->assertEquals(40, $stok->saldo);

        $this->assertDatabaseHas('log_audit', [
            'aksi' => 'stock_adjustment'
        ]);
    }
}
