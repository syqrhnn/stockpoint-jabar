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
    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('admin.dashboard');

    // Master Data Views
    Route::get('/barang', [\App\Http\Controllers\BarangController::class, 'viewIndex'])->name('admin.barang.index');
    Route::get('/gudang', [\App\Http\Controllers\GudangController::class, 'viewIndex'])->name('admin.gudang.index');
    Route::get('/supplier', [\App\Http\Controllers\SupplierController::class, 'viewIndex'])->name('admin.supplier.index');
    Route::get('/pengguna', [\App\Http\Controllers\UserController::class, 'viewIndex'])->name('admin.pengguna.index');
    Route::get('/system-check', [\App\Http\Controllers\SystemCheckController::class, 'index'])->name('admin.system-check');

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

    Route::middleware('role:admin_gudang')->group(function () {
        Route::get('/opening-balance', [\App\Http\Controllers\OpeningBalanceController::class, 'viewIndex'])->name('stok.opening-balance');
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

        Route::middleware('role:admin_gudang')->group(function () {
            Route::get('/opening-balance', [\App\Http\Controllers\OpeningBalanceController::class, 'getData']);
            Route::post('/opening-balance', [\App\Http\Controllers\OpeningBalanceController::class, 'store']);
        });
    });
});

// ROP MODULE
Route::middleware(['auth.custom', 'role:admin_gudang,kepala_gudang,staf_gudang,manajer_operasional'])->prefix('rop')->group(function () {
    Route::get('/', [\App\Http\Controllers\RopController::class, 'indexView'])->name('rop.index');
    Route::get('/api/data', [\App\Http\Controllers\RopController::class, 'getData']);
    
    // Configuration (Only Admin and Kepala Gudang)
    Route::middleware('role:admin_gudang,kepala_gudang')->group(function () {
        Route::post('/api/konfigurasi', [\App\Http\Controllers\RopController::class, 'updateParameter']);
    });
});

// Group Kepala Gudang
Route::middleware(['auth.custom', 'role:kepala_gudang,admin_gudang'])->prefix('kepala')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('kepala.dashboard');
});

// Group Staf Gudang
Route::middleware(['auth.custom', 'role:staf_gudang,kepala_gudang,admin_gudang'])->prefix('staf')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('staf.dashboard');
});

// Group Manajer Operasional
Route::middleware(['auth.custom', 'role:manajer_operasional,admin_gudang'])->prefix('manajer')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('manajer.dashboard');
});

// DASHBOARD API (All Roles)
Route::middleware(['auth.custom'])->prefix('api/dashboard')->group(function () {
    Route::get('/summary', [\App\Http\Controllers\DashboardController::class, 'getSummary']);
});

// NOTIFIKASI
Route::middleware(['auth.custom'])->group(function () {
    Route::get('/notifikasi', [\App\Http\Controllers\NotifikasiController::class, 'viewIndex'])->name('notifikasi.index');
    
    Route::prefix('api/notifikasi')->group(function () {
        Route::get('/riwayat', [\App\Http\Controllers\NotifikasiController::class, 'getRiwayat']);
        Route::get('/unread-count', [\App\Http\Controllers\NotifikasiController::class, 'getUnread']);
        Route::patch('/mark-all-read', [\App\Http\Controllers\NotifikasiController::class, 'markAllAsRead']);
        Route::patch('/{id}/read', [\App\Http\Controllers\NotifikasiController::class, 'markAsRead']);
    });
});

// LAPORAN
Route::middleware(['auth.custom'])->group(function () {
    Route::get('/laporan', [\App\Http\Controllers\LaporanController::class, 'viewIndex'])->name('laporan.index');
    Route::get('/laporan/{jenis}/export/{format}', [\App\Http\Controllers\LaporanController::class, 'export']);
    
    Route::prefix('api/laporan')->group(function () {
        Route::get('/{jenis}', [\App\Http\Controllers\LaporanController::class, 'getPreview']);
    });
});


