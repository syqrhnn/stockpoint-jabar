<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - StockPoint Jabar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8f9fa; }
        .sidebar { min-height: 100vh; background-color: #ffffff; border-right: 1px solid #e9ecef; }
        .nav-link { color: #495057; font-weight: 500; padding: 10px 20px; border-radius: 8px; margin-bottom: 5px; }
        .nav-link:hover, .nav-link.active { background-color: #e7f1ff; color: #0d6efd; }
        .nav-link i { margin-right: 10px; }
        .topbar { background-color: #ffffff; border-bottom: 1px solid #e9ecef; }
    </style>
</head>
<body>
    @php
        $role = session('role');
        $nama = session('nama');
    @endphp

    <div class="d-flex">
        <!-- Sidebar -->
        <div class="sidebar p-3" style="width: 280px;">
            <a href="/" class="d-flex align-items-center mb-4 text-decoration-none text-dark">
                <span class="fs-4 fw-bold text-primary"><i class="bi bi-box-seam"></i> StockPoint Jabar</span>
            </a>
            <hr>
            <ul class="nav flex-column mb-auto">
                @if($role === 'admin_gudang')
                    <li class="nav-item"><a href="/admin/dashboard" class="nav-link {{ request()->is('admin/dashboard') ? 'active' : '' }}"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
                    
                    <li class="nav-item mt-3 mb-1 text-muted small fw-bold text-uppercase px-3">Master Data</li>
                    <li class="nav-item"><a href="{{ route('admin.barang.index') }}" class="nav-link"><i class="bi bi-box"></i> Data Barang</a></li>
                    <li class="nav-item"><a href="{{ route('admin.gudang.index') }}" class="nav-link"><i class="bi bi-building"></i> Data Gudang</a></li>
                    <li class="nav-item"><a href="{{ route('admin.supplier.index') }}" class="nav-link"><i class="bi bi-truck"></i> Data Supplier</a></li>
                    <li class="nav-item"><a href="{{ route('admin.pengguna.index') }}" class="nav-link"><i class="bi bi-people"></i> Manajemen Pengguna</a></li>
                    
                    <li class="nav-item mt-3 mb-1 text-muted small fw-bold text-uppercase px-3">Operasional</li>
                    <li class="nav-item"><a href="{{ route('stok.catat') }}" class="nav-link"><i class="bi bi-journal-plus"></i> Catat Stok</a></li>
                    <li class="nav-item"><a href="{{ route('stok.adjustment') }}" class="nav-link"><i class="bi bi-sliders"></i> Koreksi Stok</a></li>
                    <li class="nav-item"><a href="{{ route('stok.riwayat') }}" class="nav-link"><i class="bi bi-clock-history"></i> Riwayat Transaksi</a></li>
                    
                    <li class="nav-item mt-3 mb-1 text-muted small fw-bold text-uppercase px-3">Laporan</li>
                    <li class="nav-item"><a href="#" class="nav-link"><i class="bi bi-file-earmark-text"></i> Laporan</a></li>
                
                @elseif($role === 'kepala_gudang')
                    <li class="nav-item"><a href="/kepala/dashboard" class="nav-link {{ request()->is('kepala/dashboard') ? 'active' : '' }}"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
                    
                    <li class="nav-item mt-3 mb-1 text-muted small fw-bold text-uppercase px-3">Operasional</li>
                    <li class="nav-item"><a href="{{ route('stok.catat') }}" class="nav-link"><i class="bi bi-journal-plus"></i> Catat Stok</a></li>
                    <li class="nav-item"><a href="{{ route('stok.adjustment') }}" class="nav-link"><i class="bi bi-sliders"></i> Koreksi Stok</a></li>
                    <li class="nav-item"><a href="{{ route('stok.riwayat') }}" class="nav-link"><i class="bi bi-clock-history"></i> Riwayat Transaksi</a></li>
                
                @elseif($role === 'staf_gudang')
                    <li class="nav-item"><a href="/staf/dashboard" class="nav-link {{ request()->is('staf/dashboard') ? 'active' : '' }}"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
                    
                    <li class="nav-item mt-3 mb-1 text-muted small fw-bold text-uppercase px-3">Operasional</li>
                    <li class="nav-item"><a href="{{ route('stok.catat') }}" class="nav-link"><i class="bi bi-journal-plus"></i> Catat Stok</a></li>
                    <li class="nav-item"><a href="{{ route('stok.riwayat') }}" class="nav-link"><i class="bi bi-clock-history"></i> Riwayat Transaksi</a></li>
                
                @elseif($role === 'manajer_operasional')
                    <li class="nav-item"><a href="/manajer/dashboard" class="nav-link {{ request()->is('manajer/dashboard') ? 'active' : '' }}"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
                    
                    <li class="nav-item mt-3 mb-1 text-muted small fw-bold text-uppercase px-3">Laporan</li>
                    <li class="nav-item"><a href="{{ route('stok.riwayat') }}" class="nav-link"><i class="bi bi-clock-history"></i> Riwayat Transaksi</a></li>
                @endif
            </ul>
        </div>

        <!-- Main Content -->
        <div class="flex-grow-1">
            <!-- Header -->
            <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom px-4 py-3">
                <div class="container-fluid">
                    <span class="navbar-brand mb-0 h1">@yield('title', 'Dashboard')</span>
                    
                    <div class="d-flex align-items-center">
                        <button class="btn btn-light position-relative me-3">
                            <i class="bi bi-bell"></i>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                0
                            </span>
                        </button>
                        
                        <div class="me-3 text-end">
                            <div class="fw-bold">{{ session('nama') }}</div>
                            <small class="badge bg-secondary">{{ str_replace('_', ' ', strtoupper(session('role'))) }}</small>
                        </div>
                        
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger btn-sm">Logout</button>
                        </form>
                    </div>
                </div>
            </nav>

            <!-- Content -->
            <div class="p-4">
                @yield('content')
            </div>
        </div>
    </div>

    <!-- Toast Container -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1055;">
        <div id="liveToast" class="toast align-items-center text-white border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body" id="toastMessage"></div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function showToast(message, type = 'success') {
            const toastEl = document.getElementById('liveToast');
            const toastMessage = document.getElementById('toastMessage');
            
            toastEl.classList.remove('bg-success', 'bg-danger', 'bg-warning', 'bg-info');
            
            if(type === 'success') toastEl.classList.add('bg-success');
            else if(type === 'error') toastEl.classList.add('bg-danger');
            else if(type === 'warning') toastEl.classList.add('bg-warning');
            else toastEl.classList.add('bg-info');

            toastMessage.textContent = message;
            
            const toast = new bootstrap.Toast(toastEl);
            toast.show();
        }
    </script>
    @stack('scripts')
</body>
</html>
