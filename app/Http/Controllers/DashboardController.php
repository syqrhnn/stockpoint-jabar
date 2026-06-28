<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Notifikasi;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $role = $request->session()->get('role');
        
        $gudang_list = [];
        if (in_array($role, ['admin_gudang', 'manajer_operasional'])) {
            $gudang_list = DB::table('gudang')->get();
        }

        return view('dashboard.index', compact('gudang_list'));
    }

    public function getSummary(Request $request)
    {
        $role = $request->session()->get('role');
        $session_gudang_id = $request->session()->get('gudang_id');

        $gudang_filter = null;
        if (!in_array($role, ['admin_gudang', 'manajer_operasional'])) {
            $gudang_filter = $session_gudang_id;
        } elseif ($request->filled('gudang_id')) {
            $gudang_filter = $request->gudang_id;
        }

        // 1. CARDS SUMMARY
        $qStok = DB::table('stok')->where('status', '!=', 'belum_dikonfigurasi');
        if ($gudang_filter) $qStok->where('gudang_id', $gudang_filter);
        
        $total_sku = (clone $qStok)->count();
        $total_kritis = (clone $qStok)->where('status', 'kritis')->count();
        $total_menipis = (clone $qStok)->where('status', 'menipis')->count();

        $pct_kritis = $total_sku > 0 ? round(($total_kritis / $total_sku) * 100, 1) : 0;
        $pct_menipis = $total_sku > 0 ? round(($total_menipis / $total_sku) * 100, 1) : 0;

        $gudang_aktif = $gudang_filter ? 1 : DB::table('gudang')->count();

        // 2. PRIORITY TABLE (Kritis & Menipis)
        $qPriority = DB::table('stok')
            ->join('barang', 'stok.barang_id', '=', 'barang.id')
            ->join('gudang', 'stok.gudang_id', '=', 'gudang.id')
            ->leftJoin('rop_parameter', function ($join) {
                $join->on('stok.barang_id', '=', 'rop_parameter.barang_id')
                     ->on('stok.gudang_id', '=', 'rop_parameter.gudang_id');
            })
            ->whereIn('stok.status', ['kritis', 'menipis']);
            
        if ($gudang_filter) $qPriority->where('stok.gudang_id', $gudang_filter);

        if ($request->filled('status')) {
            $qPriority->where('stok.status', $request->status);
        }

        $priority_items = $qPriority->select(
                'barang.nama as barang_nama',
                'gudang.nama as gudang_nama',
                'stok.saldo',
                'rop_parameter.rop',
                'stok.status'
            )
            ->orderByRaw("CASE WHEN stok.status = 'kritis' THEN 1 ELSE 2 END ASC")
            ->orderBy('stok.saldo', 'asc')
            ->limit(10)
            ->get();

        // 3. GUDANG SUMMARY
        $qGudangSum = DB::table('stok')
            ->join('gudang', 'stok.gudang_id', '=', 'gudang.id');
        if ($gudang_filter) $qGudangSum->where('stok.gudang_id', $gudang_filter);

        $gudang_summary_raw = $qGudangSum->select('gudang.nama as gudang_nama', 'stok.status', DB::raw('count(*) as total'))
            ->groupBy('gudang.nama', 'stok.status')
            ->get();

        $gudang_summary = [];
        foreach ($gudang_summary_raw as $row) {
            $name = $row->gudang_nama;
            if (!isset($gudang_summary[$name])) {
                $gudang_summary[$name] = ['total' => 0, 'kritis' => 0, 'menipis' => 0, 'aman' => 0];
            }
            $gudang_summary[$name]['total'] += $row->total;
            if (in_array($row->status, ['kritis', 'menipis', 'aman'])) {
                $gudang_summary[$name][$row->status] = $row->total;
            }
        }

        // 4. CHART DATA (Last 7 Days)
        $chart_labels = [];
        $chart_masuk = [];
        $chart_keluar = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $chart_labels[] = Carbon::now()->subDays($i)->format('d M');

            $qMasuk = DB::table('transaksi_stok')->where('tanggal', $date)->where('jenis', 'masuk');
            $qKeluar = DB::table('transaksi_stok')->where('tanggal', $date)->where('jenis', 'keluar');

            if ($gudang_filter) {
                $qMasuk->where('gudang_id', $gudang_filter);
                $qKeluar->where('gudang_id', $gudang_filter);
            }

            $chart_masuk[] = (int) $qMasuk->sum('jumlah');
            $chart_keluar[] = (int) $qKeluar->sum('jumlah');
        }

        // 5. NOTIFIKASI
        $qNotif = Notifikasi::orderBy('created_at', 'desc')->limit(5);
        if ($role !== 'admin_gudang' && $role !== 'manajer_operasional') {
            $qNotif->where('gudang_id', $session_gudang_id);
        }
        $notifikasi = $qNotif->get();
        
        $notifikasi->transform(function ($n) {
            $n->judul = "Peringatan Stok"; // Placeholder judul karena tabel baru tidak punya judul
            $n->is_read = ($n->status === 'sudah_dibaca');
            $n->link = route('notifikasi.index'); // Redirect ke notifikasi index
            return $n;
        });

        $unread_notif = Notifikasi::where('status', 'belum_dibaca');
        if ($role !== 'admin_gudang' && $role !== 'manajer_operasional') {
            $unread_notif->where('gudang_id', $session_gudang_id);
        }
        $unread_count = $unread_notif->count();

        return response()->json([
            'success' => true,
            'cards' => [
                'total_sku' => $total_sku,
                'kritis' => $total_kritis,
                'pct_kritis' => $pct_kritis,
                'menipis' => $total_menipis,
                'pct_menipis' => $pct_menipis,
                'gudang_aktif' => $gudang_aktif
            ],
            'priority' => $priority_items,
            'gudang_summary' => $gudang_summary,
            'chart' => [
                'labels' => $chart_labels,
                'masuk' => $chart_masuk,
                'keluar' => $chart_keluar
            ],
            'notifikasi' => $notifikasi,
            'unread_notif' => $unread_count
        ]);
    }
}
