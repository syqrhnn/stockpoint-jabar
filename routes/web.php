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

    // Master Data Views
    Route::get('/barang', [\App\Http\Controllers\BarangController::class, 'viewIndex'])->name('admin.barang.index');
    Route::get('/gudang', [\App\Http\Controllers\GudangController::class, 'viewIndex'])->name('admin.gudang.index');
    Route::get('/supplier', [\App\Http\Controllers\SupplierController::class, 'viewIndex'])->name('admin.supplier.index');
    Route::get('/pengguna', [\App\Http\Controllers\UserController::class, 'viewIndex'])->name('admin.pengguna.index');

    // Master Data APIs
    Route::prefix('api')->group(function() {
        Route::apiResource('barang', \App\Http\Controllers\BarangController::class)->except(['show']);
        Route::apiResource('gudang', \App\Http\Controllers\GudangController::class)->except(['show']);
        Route::apiResource('supplier', \App\Http\Controllers\SupplierController::class)->except(['show']);
        
        // Users API
        Route::get('/pengguna/gudang-list', [\App\Http\Controllers\UserController::class, 'getGudangList']);
        Route::apiResource('pengguna', \App\Http\Controllers\UserController::class)->except(['show']);
        Route::patch('/pengguna/{id}/deactivate', [\App\Http\Controllers\UserController::class, 'deactivate']);
    });
});

// STOK MANAGEMENT (Various Roles)
Route::middleware(['auth.custom', 'role:admin_gudang,kepala_gudang,staf_gudang,manajer_operasional'])->prefix('stok')->group(function () {
    // Views
    Route::get('/riwayat', [\App\Http\Controllers\StokController::class, 'viewRiwayat'])->name('stok.riwayat');

    Route::middleware('role:admin_gudang,kepala_gudang,staf_gudang')->group(function () {
        Route::get('/catat', [\App\Http\Controllers\StokController::class, 'viewCatat'])->name('stok.catat');
    });

    Route::middleware('role:admin_gudang,kepala_gudang')->group(function () {
        Route::get('/adjustment', [\App\Http\Controllers\StokController::class, 'viewAdjustment'])->name('stok.adjustment');
    });

    // API
    Route::prefix('api')->group(function () {
        Route::get('/riwayat', [\App\Http\Controllers\Api\StokApiController::class, 'getRiwayat']);
        Route::get('/saldo', [\App\Http\Controllers\Api\StokApiController::class, 'getSaldo']);

        Route::middleware('role:admin_gudang,kepala_gudang,staf_gudang')->group(function () {
            Route::post('/masuk', [\App\Http\Controllers\Api\StokApiController::class, 'storeMasuk']);
            Route::post('/keluar', [\App\Http\Controllers\Api\StokApiController::class, 'storeKeluar']);
        });

        Route::middleware('role:admin_gudang,kepala_gudang')->group(function () {
            Route::post('/adjustment', [\App\Http\Controllers\Api\StokApiController::class, 'storeAdjustment']);
        });
    });
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
