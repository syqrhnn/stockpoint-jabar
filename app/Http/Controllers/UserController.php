<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function viewIndex()
    {
        return view('admin.user.index');
    }

    public function index(Request $request)
    {
        $query = DB::table('users')
            ->leftJoin('gudang', 'users.gudang_id', '=', 'gudang.id')
            ->select('users.*', 'gudang.nama as gudang_nama')
            ->orderBy('users.id', 'desc');

        if ($request->has('search') && $request->search != '') {
            $query->where(function($q) use ($request) {
                $q->where('users.nama', 'like', '%' . $request->search . '%')
                  ->orWhere('users.email', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->has('role') && $request->role != '') {
            $query->where('users.role', $request->role);
        }

        if ($request->has('is_active') && $request->is_active != '') {
            $query->where('users.is_active', $request->is_active);
        }

        $data = $query->paginate(10);
        
        return response()->json([
            'success' => true,
            'message' => 'Data pengguna berhasil diambil',
            'data' => $data
        ]);
    }

    public function getGudangList()
    {
        $gudang = DB::table('gudang')->select('id', 'nama')->orderBy('nama')->get();
        return response()->json([
            'success' => true,
            'data' => $gudang
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|max:100',
            'email' => 'required|email|unique:users,email|max:255',
            'password' => 'required|min:8',
            'role' => ['required', Rule::in(['admin_gudang', 'kepala_gudang', 'staf_gudang', 'manajer_operasional'])],
        ]);

        $gudang_id = null;
        if (in_array($request->role, ['kepala_gudang', 'staf_gudang'])) {
            $request->validate([
                'gudang_id' => 'required|exists:gudang,id'
            ]);
            $gudang_id = $request->gudang_id;
        }

        $id = DB::table('users')->insertGetId([
            'nama' => $request->nama,
            'email' => $request->email,
            'password_hash' => Hash::make($request->password),
            'role' => $request->role,
            'gudang_id' => $gudang_id,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pengguna berhasil ditambahkan',
            'data' => DB::table('users')->find($id)
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama' => 'required|max:100',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($id)
            ],
            'role' => ['required', Rule::in(['admin_gudang', 'kepala_gudang', 'staf_gudang', 'manajer_operasional'])],
        ]);

        $gudang_id = null;
        if (in_array($request->role, ['kepala_gudang', 'staf_gudang'])) {
            $request->validate([
                'gudang_id' => 'required|exists:gudang,id'
            ]);
            $gudang_id = $request->gudang_id;
        }

        DB::table('users')->where('id', $id)->update([
            'nama' => $request->nama,
            'email' => $request->email,
            'role' => $request->role,
            'gudang_id' => $gudang_id,
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pengguna berhasil diperbarui',
            'data' => DB::table('users')->find($id)
        ]);
    }

    public function deactivate($id)
    {
        if (session('user_id') == $id) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak dapat menonaktifkan akun Anda sendiri.'
            ], 403);
        }

        DB::table('users')->where('id', $id)->update([
            'is_active' => false,
            'updated_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Akun pengguna berhasil dinonaktifkan.'
        ]);
    }
}
