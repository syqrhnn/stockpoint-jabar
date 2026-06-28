<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\StokService;
use App\Services\NotifikasiService;

class OpeningBalanceController extends Controller
{
    protected StokService $stokService;

    public function __construct(StokService $stokService)
    {
        $this->stokService = $stokService;
    }

    /**
     * Tampilkan halaman Opening Balance
     */
    public function viewIndex()
    {
        $barang = DB::table('barang')->orderBy('nama')->get();
        $gudang = DB::table('gudang')->orderBy('nama')->get();

        return view('stok.opening-balance', compact('barang', 'gudang'));
    }

    /**
     * API: Ambil data kombinasi barang x gudang beserta status OB
     */
    public function getData()
    {
        $barang = DB::table('barang')->orderBy('nama')->get();
        $gudang = DB::table('gudang')->orderBy('nama')->get();

        $items = [];
        $totalKombinasi = 0;
        $sudahAdaSaldo = 0;

        foreach ($gudang as $g) {
            foreach ($barang as $b) {
                $totalKombinasi++;

                $stok = DB::table('stok')
                    ->where('barang_id', $b->id)
                    ->where('gudang_id', $g->id)
                    ->first();

                $adaTransaksi = DB::table('transaksi_stok')
                    ->where('barang_id', $b->id)
                    ->where('gudang_id', $g->id)
                    ->exists();

                $saldo = $stok ? $stok->saldo : 0;
                $sudahInput = ($saldo > 0 || $adaTransaksi);

                if ($sudahInput) $sudahAdaSaldo++;

                $items[] = [
                    'barang_id' => $b->id,
                    'barang_nama' => $b->nama,
                    'gudang_id' => $g->id,
                    'gudang_nama' => $g->nama,
                    'saldo' => $saldo,
                    'sudah_input' => $sudahInput,
                ];
            }
        }

        return response()->json([
            'success' => true,
            'items' => $items,
            'total' => $totalKombinasi,
            'done' => $sudahAdaSaldo,
        ]);
    }

    /**
     * POST: Simpan opening balance secara batch
     */
    public function store(Request $request)
    {
        $request->validate([
            'entries' => 'required|array|min:1',
            'entries.*.barang_id' => 'required|integer|exists:barang,id',
            'entries.*.gudang_id' => 'required|integer|exists:gudang,id',
            'entries.*.jumlah' => 'required|integer|min:0',
        ]);

        $user = (object) [
            'id' => $request->session()->get('user_id'),
            'role' => $request->session()->get('role'),
            'gudang_id' => $request->session()->get('gudang_id'),
        ];

        $saved = 0;
        $errors = [];

        foreach ($request->entries as $entry) {
            if ($entry['jumlah'] <= 0) continue;

            try {
                $this->stokService->catatStokMasuk([
                    'barang_id' => $entry['barang_id'],
                    'gudang_id' => $entry['gudang_id'],
                    'jumlah' => $entry['jumlah'],
                    'tanggal' => now()->toDateString(),
                    'supplier_id' => null,
                    'catatan' => 'Opening Balance - Go-Live',
                ], $user);

                $saved++;
            } catch (\Exception $e) {
                $errors[] = "Barang #{$entry['barang_id']} Gudang #{$entry['gudang_id']}: {$e->getMessage()}";
            }
        }

        return response()->json([
            'success' => true,
            'saved' => $saved,
            'errors' => $errors,
        ]);
    }
}
