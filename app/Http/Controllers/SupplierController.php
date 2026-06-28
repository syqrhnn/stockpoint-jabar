<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SupplierController extends Controller
{
    public function viewIndex()
    {
        return view('admin.supplier.index');
    }

    public function index(Request $request)
    {
        $query = DB::table('supplier')->orderBy('id', 'desc');

        if ($request->has('search') && $request->search != '') {
            $query->where('nama', 'like', '%' . $request->search . '%')
                  ->orWhere('kontak', 'like', '%' . $request->search . '%');
        }

        $data = $query->paginate(10);
        return response()->json([
            'success' => true,
            'message' => 'Data supplier berhasil diambil',
            'data' => $data
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|max:100',
            'kontak' => 'required|max:100',
            'lead_time_default' => 'required|integer|min:1',
        ]);

        $id = DB::table('supplier')->insertGetId([
            'nama' => $request->nama,
            'kontak' => $request->kontak,
            'lead_time_default' => $request->lead_time_default,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Supplier berhasil ditambahkan',
            'data' => DB::table('supplier')->find($id)
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama' => 'required|max:100',
            'kontak' => 'required|max:100',
            'lead_time_default' => 'required|integer|min:1',
        ]);

        DB::table('supplier')->where('id', $id)->update([
            'nama' => $request->nama,
            'kontak' => $request->kontak,
            'lead_time_default' => $request->lead_time_default,
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Supplier berhasil diperbarui',
            'data' => DB::table('supplier')->find($id)
        ]);
    }
    
    public function destroy($id)
    {
        DB::table('supplier')->where('id', $id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Supplier berhasil dihapus'
        ]);
    }
}
