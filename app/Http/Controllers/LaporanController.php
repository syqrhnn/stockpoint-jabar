<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\StokExport;
use App\Exports\TransaksiExport;
use App\Exports\RopExport;

class LaporanController extends Controller
{
    /**
     * View untuk halaman laporan utama
     */
    public function viewIndex()
    {
        $role = session('role');
        $gudang_list = [];

        if (in_array($role, ['admin_gudang', 'manajer_operasional'])) {
            $gudang_list = DB::table('gudang')->get();
        } else {
            $gudang_id = session('gudang_id');
            $gudang_list = DB::table('gudang')->where('id', $gudang_id)->get();
        }

        return view('laporan.index', compact('gudang_list'));
    }

    /**
     * Membangun Query Dasar (Role-based)
     */
    private function getBaseQuery(Request $request, $jenis)
    {
        $role = $request->session()->get('role');
        $gudang_id = $request->session()->get('gudang_id');

        // Gunakan filter gudang dari request jika ada, tapi jika bukan admin/manajer paksa ke session
        $filter_gudang = $request->gudang_id;
        if (!in_array($role, ['admin_gudang', 'manajer_operasional'])) {
            $filter_gudang = $gudang_id;
        }

        if ($jenis === 'stok') {
            $q = DB::table('stok')
                ->join('barang', 'stok.barang_id', '=', 'barang.id')
                ->join('gudang', 'stok.gudang_id', '=', 'gudang.id')
                ->leftJoin('rop_parameter', function ($join) {
                    $join->on('stok.barang_id', '=', 'rop_parameter.barang_id')
                         ->on('stok.gudang_id', '=', 'rop_parameter.gudang_id');
                })
                ->select(
                    'barang.nama as barang_nama',
                    'gudang.nama as gudang_nama',
                    'stok.saldo',
                    'rop_parameter.rop',
                    'stok.status'
                );
            if ($filter_gudang) $q->where('stok.gudang_id', $filter_gudang);
            if ($request->filled('status')) $q->where('stok.status', $request->status);
            return $q;
        }

        if ($jenis === 'transaksi') {
            $q = DB::table('transaksi_stok')
                ->join('barang', 'transaksi_stok.barang_id', '=', 'barang.id')
                ->join('gudang', 'transaksi_stok.gudang_id', '=', 'gudang.id')
                ->leftJoin('supplier', 'transaksi_stok.supplier_id', '=', 'supplier.id')
                ->join('users', 'transaksi_stok.user_id', '=', 'users.id')
                ->select(
                    'transaksi_stok.tanggal',
                    'barang.nama as barang_nama',
                    'gudang.nama as gudang_nama',
                    'transaksi_stok.jenis',
                    'transaksi_stok.jumlah',
                    'transaksi_stok.saldo_sebelum',
                    'transaksi_stok.saldo_sesudah',
                    'supplier.nama as supplier_nama',
                    'users.name as user_nama',
                    'transaksi_stok.catatan'
                );

            if ($filter_gudang) $q->where('transaksi_stok.gudang_id', $filter_gudang);
            if ($request->filled('tanggal_mulai')) $q->where('transaksi_stok.tanggal', '>=', $request->tanggal_mulai);
            if ($request->filled('tanggal_akhir')) $q->where('transaksi_stok.tanggal', '<=', $request->tanggal_akhir);
            if ($request->filled('jenis')) $q->where('transaksi_stok.jenis', $request->jenis);
            
            return $q->orderBy('transaksi_stok.created_at', 'desc');
        }

        if ($jenis === 'rop') {
            $q = DB::table('rop_parameter')
                ->join('barang', 'rop_parameter.barang_id', '=', 'barang.id')
                ->join('gudang', 'rop_parameter.gudang_id', '=', 'gudang.id')
                ->join('stok', function ($join) {
                    $join->on('rop_parameter.barang_id', '=', 'stok.barang_id')
                         ->on('rop_parameter.gudang_id', '=', 'stok.gudang_id');
                })
                ->select(
                    'barang.nama as barang_nama',
                    'gudang.nama as gudang_nama',
                    'rop_parameter.adu',
                    'rop_parameter.lead_time',
                    'rop_parameter.safety_stock',
                    'rop_parameter.rop',
                    'stok.saldo as stok_aktual',
                    'stok.status'
                );
            if ($filter_gudang) $q->where('rop_parameter.gudang_id', $filter_gudang);
            if ($request->filled('status')) $q->where('stok.status', $request->status);
            return $q;
        }
    }

    /**
     * Mengambil JSON Pratinjau untuk API
     */
    public function getPreview(Request $request, $jenis)
    {
        if (!in_array($jenis, ['stok', 'transaksi', 'rop'])) {
            return response()->json(['success' => false, 'message' => 'Jenis laporan tidak valid'], 400);
        }

        $query = $this->getBaseQuery($request, $jenis);
        $data = $query->get();

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Endpoint Export (PDF / Excel)
     */
    public function export(Request $request, $jenis, $format)
    {
        if (!in_array($jenis, ['stok', 'transaksi', 'rop'])) abort(404);
        if (!in_array($format, ['pdf', 'excel'])) abort(404);

        $query = $this->getBaseQuery($request, $jenis);
        $data = $query->get();

        // Tentukan nama file
        $gudang = $request->gudang_id ? strtolower(str_replace(' ', '_', DB::table('gudang')->where('id', $request->gudang_id)->value('nama'))) : 'semua_gudang';
        $tanggal = now()->format('Ymd');
        $filename = "stockpoint_{$jenis}_{$gudang}_{$tanggal}";

        if ($format === 'pdf') {
            $pdf = Pdf::loadView("laporan.pdf.{$jenis}", compact('data'));
            return $pdf->download("{$filename}.pdf");
        } 
        
        if ($format === 'excel') {
            if ($jenis === 'stok') return Excel::download(new StokExport($data), "{$filename}.xlsx");
            if ($jenis === 'transaksi') return Excel::download(new TransaksiExport($data), "{$filename}.xlsx");
            if ($jenis === 'rop') return Excel::download(new RopExport($data), "{$filename}.xlsx");
        }
    }
}
