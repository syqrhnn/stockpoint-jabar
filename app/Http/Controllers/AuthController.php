<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        // If already logged in, redirect to dashboard based on role
        if (session()->has('user_id')) {
            return $this->redirectBasedOnRole(session('role'));
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = DB::table('users')->where('email', $request->email)->first();

        // Check if user exists and password is correct
        if (!$user || !Hash::check($request->password, $user->password_hash)) {
            // Generic error message, not indicating which field is wrong
            return back()->withErrors(['error' => 'Email atau password salah']);
        }

        // Check if user is active
        if (!$user->is_active) {
            return back()->withErrors(['error' => 'Email atau password salah']); // Keep it generic or indicate inactive? The prompt says "tolak dengan pesan generik". So we keep it generic to avoid enumeration, or "Akun tidak aktif". The prompt says "jika false → tolak dengan pesan generik" and later "Jika gagal: pesan error generik ('Email atau password salah') — JANGAN indikasikan field mana yang salah". Let's stick to the generic one for everything. Wait, I will use exactly "Email atau password salah".
        }

        // If success: create session with user_id, role, gudang_id
        session([
            'user_id' => $user->id,
            'role' => $user->role,
            'gudang_id' => $user->gudang_id,
            'nama' => $user->nama, // Storing name for header display
        ]);

        return $this->redirectBasedOnRole($user->role);
    }

    public function logout(Request $request)
    {
        $request->session()->flush();
        return redirect('/login');
    }

    private function redirectBasedOnRole($role)
    {
        switch ($role) {
            case 'admin_gudang':
                return redirect('/admin/dashboard');
            case 'kepala_gudang':
                return redirect('/kepala/dashboard');
            case 'staf_gudang':
                return redirect('/staf/dashboard');
            case 'manajer_operasional':
                return redirect('/manajer/dashboard');
            default:
                return redirect('/login');
        }
    }
}
