<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class BarangController extends Controller
{
    public function viewIndex()
    {
        return view('admin.barang.index');
    }

    public function index(Request $request)
    {
        $query = DB::table('barang')->orderBy('id', 'desc');

        if ($request->has('search') && $request->search != '') {
            $query->where('nama', 'like', '%' . $request->search . '%');
        }

        if ($request->has('kategori') && $request->kategori != '') {
            $query->where('kategori', $request->kategori);
        }

        $data = $query->paginate(10);
        return response()->json([
            'success' => true,
            'message' => 'Data barang berhasil diambil',
            'data' => $data
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|unique:barang,nama|max:100',
            'kategori' => 'required|max:50',
            'satuan' => 'required|max:20',
        ]);

        $id = DB::table('barang')->insertGetId([
            'nama' => $request->nama,
            'kategori' => $request->kategori,
            'satuan' => $request->satuan,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Barang berhasil ditambahkan',
            'data' => DB::table('barang')->find($id)
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama' => [
                'required',
                'max:100',
                Rule::unique('barang', 'nama')->ignore($id)
            ],
            'kategori' => 'required|max:50',
            'satuan' => 'required|max:20',
        ]);

        DB::table('barang')->where('id', $id)->update([
            'nama' => $request->nama,
            'kategori' => $request->kategori,
            'satuan' => $request->satuan,
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Barang berhasil diperbarui',
            'data' => DB::table('barang')->find($id)
        ]);
    }

    public function destroy($id)
    {
        if (Schema::hasTable('transaksi_stok')) {
            $exists = DB::table('transaksi_stok')->where('barang_id', $id)->exists();
            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Barang tidak dapat dihapus karena sudah memiliki riwayat transaksi.'
                ], 400);
            }
        }

        DB::table('barang')->where('id', $id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Barang berhasil dihapus'
        ]);
    }
}
