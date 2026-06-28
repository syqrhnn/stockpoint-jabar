<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\StokService;

class RopController extends Controller
{
    protected StokService $stokService;

    public function __construct(StokService $stokService)
    {
        $this->stokService = $stokService;
    }

    public function indexView(Request $request)
    {
        $role = $request->session()->get('role');
        
        $gudang = [];
        if (in_array($role, ['admin_gudang', 'manajer_operasional'])) {
            $gudang = DB::table('gudang')->get();
        } else {
            $gudang_id = $request->session()->get('gudang_id');
            $gudang = DB::table('gudang')->where('id', $gudang_id)->get();
        }

        $barang = DB::table('barang')->get();

        return view('rop.index', compact('gudang', 'barang'));
    }

    public function getData(Request $request)
    {
        $role = $request->session()->get('role');
        $gudang_id_session = $request->session()->get('gudang_id');

        $query = DB::table('stok')
            ->join('barang', 'stok.barang_id', '=', 'barang.id')
            ->join('gudang', 'stok.gudang_id', '=', 'gudang.id')
            ->leftJoin('rop_parameter', function ($join) {
                $join->on('stok.barang_id', '=', 'rop_parameter.barang_id')
                     ->on('stok.gudang_id', '=', 'rop_parameter.gudang_id');
            })
            ->select(
                'stok.barang_id',
                'barang.nama as barang_nama',
                'barang.satuan',
                'stok.gudang_id',
                'gudang.nama as gudang_nama',
                'stok.saldo as saldo_aktual',
                'stok.status',
                'rop_parameter.adu',
                'rop_parameter.lead_time',
                'rop_parameter.safety_stock',
                'rop_parameter.rop'
            );

        if (!in_array($role, ['admin_gudang', 'manajer_operasional'])) {
            $query->where('stok.gudang_id', $gudang_id_session);
        } elseif ($request->filled('gudang_id')) {
            $query->where('stok.gudang_id', $request->gudang_id);
        }

        if ($request->filled('status')) {
            $query->where('stok.status', $request->status);
        }

        // Custom order for priority
        $query->orderByRaw("
            CASE 
                WHEN stok.status = 'kritis' THEN 1
                WHEN stok.status = 'menipis' THEN 2
                WHEN stok.status = 'aman' THEN 3
                ELSE 4
            END ASC
        ")->orderBy('barang.nama', 'asc');

        $data = $query->get();

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    public function updateParameter(Request $request)
    {
        $request->validate([
            'barang_id' => 'required|exists:barang,id',
            'gudang_id' => 'required|exists:gudang,id',
            'lead_time' => 'required|integer|min:1',
            'safety_stock' => 'required|integer|min:0',
        ]);

        $user = (object) [
            'id' => $request->session()->get('user_id'),
            'role' => $request->session()->get('role'),
            'gudang_id' => $request->session()->get('gudang_id'),
        ];

        try {
            $result = $this->stokService->simpanParameterRop($request->all(), $user);
            return response()->json([
                'success' => true,
                'message' => 'Konfigurasi ROP berhasil disimpan.',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 403);
        }
    }
}
