<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class GudangController extends Controller
{
    public function viewIndex()
    {
        return view('admin.gudang.index');
    }

    public function index(Request $request)
    {
        $query = DB::table('gudang')->orderBy('id', 'desc');

        if ($request->has('search') && $request->search != '') {
            $query->where('nama', 'like', '%' . $request->search . '%')
                  ->orWhere('lokasi', 'like', '%' . $request->search . '%');
        }

        $data = $query->paginate(10);
        return response()->json([
            'success' => true,
            'message' => 'Data gudang berhasil diambil',
            'data' => $data
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|unique:gudang,nama|max:100',
            'lokasi' => 'required|max:200',
            'kapasitas' => 'required|integer|min:1',
        ]);

        $id = DB::table('gudang')->insertGetId([
            'nama' => $request->nama,
            'lokasi' => $request->lokasi,
            'kapasitas' => $request->kapasitas,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Gudang berhasil ditambahkan',
            'data' => DB::table('gudang')->find($id)
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama' => [
                'required',
                'max:100',
                Rule::unique('gudang', 'nama')->ignore($id)
            ],
            'lokasi' => 'required|max:200',
            'kapasitas' => 'required|integer|min:1',
        ]);

        DB::table('gudang')->where('id', $id)->update([
            'nama' => $request->nama,
            'lokasi' => $request->lokasi,
            'kapasitas' => $request->kapasitas,
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Gudang berhasil diperbarui',
            'data' => DB::table('gudang')->find($id)
        ]);
    }
    
    public function destroy($id)
    {
        // Simple delete for gudang
        DB::table('gudang')->where('id', $id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Gudang berhasil dihapus'
        ]);
    }
}
