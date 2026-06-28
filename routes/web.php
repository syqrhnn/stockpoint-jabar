<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;

Route::get('/', function () {
    return redirect('/login');
});

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Group Admin
Route::middleware(['auth.custom', 'role:admin_gudang'])->prefix('admin')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard.index');
    })->name('admin.dashboard');
});

// Group Kepala Gudang
Route::middleware(['auth.custom', 'role:kepala_gudang,admin_gudang'])->prefix('kepala')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard.index');
    })->name('kepala.dashboard');
});

// Group Staf Gudang
Route::middleware(['auth.custom', 'role:staf_gudang,kepala_gudang,admin_gudang'])->prefix('staf')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard.index');
    })->name('staf.dashboard');
});

// Group Manajer Operasional
Route::middleware(['auth.custom', 'role:manajer_operasional,admin_gudang'])->prefix('manajer')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard.index');
    })->name('manajer.dashboard');
});
