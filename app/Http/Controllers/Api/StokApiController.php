<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\StokService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class StokApiController extends Controller
{
    protected StokService $stokService;

    public function __construct(StokService $stokService)
    {
        $this->stokService = $stokService;
    }

    private function getCurrentUser()
    {
        return (object)[
            'id' => session('user_id'),
            'role' => session('role'),
            'gudang_id' => session('gudang_id')
        ];
    }

    public function getSaldo(Request $request)
    {
        $request->validate([
            'barang_id' => 'required|exists:barang,id',
            'gudang_id' => 'required|exists:gudang,id'
        ]);

        $stok = DB::table('stok')
            ->where('barang_id', $request->barang_id)
            ->where('gudang_id', $request->gudang_id)
            ->first();

        return response()->json([
            'success' => true,
            'saldo' => $stok ? $stok->saldo : 0
        ]);
    }

    public function storeMasuk(Request $request)
    {
        $request->validate([
            'barang_id' => 'required|exists:barang,id',
            'gudang_id' => 'required|exists:gudang,id',
            'jumlah' => 'required|integer|min:1',
            'tanggal' => 'required|date|before_or_equal:today',
            'supplier_id' => 'nullable|exists:supplier,id',
            'catatan' => 'nullable|string'
        ]);

        try {
            $transaksi = $this->stokService->catatStokMasuk($request->all(), $this->getCurrentUser());
            return response()->json([
                'success' => true,
                'message' => 'Stok masuk berhasil dicatat',
                'data' => $transaksi
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function storeKeluar(Request $request)
    {
        $request->validate([
            'barang_id' => 'required|exists:barang,id',
            'gudang_id' => 'required|exists:gudang,id',
            'jumlah' => 'required|integer|min:1',
            'tanggal' => 'required|date|before_or_equal:today',
            'catatan' => 'nullable|string'
        ]);

        try {
            $transaksi = $this->stokService->catatStokKeluar($request->all(), $this->getCurrentUser());
            return response()->json([
                'success' => true,
                'message' => 'Stok keluar berhasil dicatat',
                'data' => $transaksi
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function storeAdjustment(Request $request)
    {
        $request->validate([
            'barang_id' => 'required|exists:barang,id',
            'gudang_id' => 'required|exists:gudang,id',
            'jumlah_koreksi' => 'required|integer',
            'catatan' => 'required|string|min:5'
        ]);

        try {
            $transaksi = $this->stokService->stockAdjustment($request->all(), $this->getCurrentUser());
            return response()->json([
                'success' => true,
                'message' => 'Koreksi stok berhasil dicatat',
                'data' => $transaksi
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function getRiwayat(Request $request)
    {
        $query = DB::table('transaksi_stok')
            ->join('barang', 'transaksi_stok.barang_id', '=', 'barang.id')
            ->join('gudang', 'transaksi_stok.gudang_id', '=', 'gudang.id')
            ->join('users', 'transaksi_stok.user_id', '=', 'users.id')
            ->leftJoin('supplier', 'transaksi_stok.supplier_id', '=', 'supplier.id')
            ->select(
                'transaksi_stok.*',
                'barang.nama as barang_nama',
                'gudang.nama as gudang_nama',
                'users.nama as user_nama',
                'supplier.nama as supplier_nama'
            )
            ->orderBy('transaksi_stok.id', 'desc');

        // Filter berdasarkan akses gudang (jika bukan admin/manajer)
        $userRole = session('role');
        if (in_array($userRole, ['kepala_gudang', 'staf_gudang'])) {
            $query->where('transaksi_stok.gudang_id', session('gudang_id'));
        } elseif ($request->filled('gudang_id')) {
            $query->where('transaksi_stok.gudang_id', $request->gudang_id);
        }

        if ($request->filled('tanggal_dari')) {
            $query->where('transaksi_stok.tanggal', '>=', $request->tanggal_dari);
        }
        if ($request->filled('tanggal_sampai')) {
            $query->where('transaksi_stok.tanggal', '<=', $request->tanggal_sampai);
        }
        if ($request->filled('barang_id')) {
            $query->where('transaksi_stok.barang_id', $request->barang_id);
        }
        if ($request->filled('jenis')) {
            $query->where('transaksi_stok.jenis', $request->jenis);
        }

        $data = $query->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }
}
