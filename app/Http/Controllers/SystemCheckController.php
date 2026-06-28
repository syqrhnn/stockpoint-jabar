<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SystemCheckController extends Controller
{
    public function index()
    {
        // 1. Gudang terdaftar (minimal 3)
        $gudangCount = DB::table('gudang')->count();
        $gudangOk = $gudangCount >= 3;

        // 2. Barang sudah diinput
        $barangCount = DB::table('barang')->count();
        $barangOk = $barangCount > 0;

        // 3. Supplier sudah diinput
        $supplierCount = DB::table('supplier')->count();
        $supplierOk = $supplierCount > 0;

        // 4. Semua akun pengguna sudah dibuat (min: 1 admin, 1 kepala, 1 staf, 1 manajer)
        $userAdmin = DB::table('users')->where('role', 'admin_gudang')->count();
        $userKepala = DB::table('users')->where('role', 'kepala_gudang')->count();
        $userStaf = DB::table('users')->where('role', 'staf_gudang')->count();
        $userManajer = DB::table('users')->where('role', 'manajer_operasional')->count();
        $userTotal = DB::table('users')->count();
        $userOk = $userAdmin >= 1 && $userKepala >= 1 && $userStaf >= 1 && $userManajer >= 1;

        // 5. Parameter ROP terkonfigurasi untuk semua kombinasi barang x gudang
        $totalKombinasi = $barangCount * $gudangCount;
        $ropConfigured = DB::table('rop_parameter')->count();
        $ropOk = $totalKombinasi > 0 && $ropConfigured >= $totalKombinasi;

        // 6. Opening balance sudah diinput untuk semua barang
        $stokWithSaldo = DB::table('stok')->where('saldo', '>', 0)->count();
        $obOk = $totalKombinasi > 0 && $stokWithSaldo >= $totalKombinasi;

        $checklist = [
            [
                'label' => "Semua gudang sudah didaftarkan (minimal 3 gudang)",
                'ok' => $gudangOk,
                'detail' => "{$gudangCount} gudang terdaftar",
            ],
            [
                'label' => "Data barang sudah diinput",
                'ok' => $barangOk,
                'detail' => "{$barangCount} barang terdaftar",
            ],
            [
                'label' => "Data supplier sudah diinput",
                'ok' => $supplierOk,
                'detail' => "{$supplierCount} supplier terdaftar",
            ],
            [
                'label' => "Semua akun pengguna sudah dibuat (Admin, Kepala, Staf, Manajer)",
                'ok' => $userOk,
                'detail' => "Total: {$userTotal} — Admin: {$userAdmin}, Kepala: {$userKepala}, Staf: {$userStaf}, Manajer: {$userManajer}",
            ],
            [
                'label' => "Parameter ROP sudah dikonfigurasi untuk semua barang di semua gudang",
                'ok' => $ropOk,
                'detail' => "{$ropConfigured} dari {$totalKombinasi} kombinasi terkonfigurasi",
            ],
            [
                'label' => "Opening balance sudah diinput untuk semua barang",
                'ok' => $obOk,
                'detail' => "{$stokWithSaldo} dari {$totalKombinasi} kombinasi memiliki saldo",
            ],
        ];

        $allOk = collect($checklist)->every(fn($item) => $item['ok']);

        return view('admin.system-check', compact('checklist', 'allOk'));
    }
}
