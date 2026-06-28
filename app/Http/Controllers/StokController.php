<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StokController extends Controller
{
    public function viewCatat()
    {
        $barang = DB::table('barang')->orderBy('nama')->get();
        $gudang = DB::table('gudang')->orderBy('nama')->get();
        $supplier = DB::table('supplier')->orderBy('nama')->get();
        
        return view('stok.catat', compact('barang', 'gudang', 'supplier'));
    }

    public function viewAdjustment()
    {
        $barang = DB::table('barang')->orderBy('nama')->get();
        $gudang = DB::table('gudang')->orderBy('nama')->get();
        
        return view('stok.adjustment', compact('barang', 'gudang'));
    }

    public function viewRiwayat()
    {
        $barang = DB::table('barang')->orderBy('nama')->get();
        $gudang = DB::table('gudang')->orderBy('nama')->get();
        
        return view('stok.riwayat', compact('barang', 'gudang'));
    }
}
