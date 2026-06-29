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
    
    <!-- Custom Design System -->
    @vite(['resources/css/stockpoint-ui.css', 'resources/js/app.js'])
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
                    <li class="nav-item"><a href="/admin/dashboard"
                            class="nav-link {{ request()->is('admin/dashboard') ? 'active' : '' }}"><i
                                class="bi bi-speedometer2"></i> Dashboard</a></li>

                    <li class="nav-item mt-3 mb-1 text-muted small fw-bold text-uppercase px-3">Master Data</li>
                    <li class="nav-item"><a href="{{ route('admin.barang.index') }}" class="nav-link"><i
                                class="bi bi-box"></i> Data Barang</a></li>
                    <li class="nav-item"><a href="{{ route('admin.gudang.index') }}" class="nav-link"><i
                                class="bi bi-building"></i> Data Gudang</a></li>
                    <li class="nav-item"><a href="{{ route('admin.supplier.index') }}" class="nav-link"><i
                                class="bi bi-truck"></i> Data Supplier</a></li>
                    <li class="nav-item"><a href="{{ route('admin.pengguna.index') }}" class="nav-link"><i
                                class="bi bi-people"></i> Manajemen Pengguna</a></li>

                    <li class="nav-item mt-3 mb-1 text-muted small fw-bold text-uppercase px-3">System & Tools</li>
                    <li class="nav-item"><a href="{{ route('stok.opening-balance') }}" class="nav-link"><i
                                class="bi bi-database-add"></i> Opening Balance</a></li>
                    <li class="nav-item"><a href="{{ route('admin.system-check') }}" class="nav-link"><i
                                class="bi bi-shield-check"></i> System Check</a></li>

                    <li class="nav-item mt-3 mb-1 text-muted small fw-bold text-uppercase px-3">Operasional</li>
                    <li class="nav-item"><a href="{{ route('stok.catat') }}" class="nav-link"><i
                                class="bi bi-journal-plus"></i> Catat Stok</a></li>
                    <li class="nav-item"><a href="{{ route('stok.adjustment') }}" class="nav-link"><i
                                class="bi bi-sliders"></i> Koreksi Stok</a></li>
                    <li class="nav-item"><a href="{{ route('rop.index') }}" class="nav-link"><i
                                class="bi bi-graph-up-arrow"></i> ROP & Parameter</a></li>
                    <li class="nav-item"><a href="{{ route('stok.riwayat') }}" class="nav-link"><i
                                class="bi bi-clock-history"></i> Riwayat Transaksi</a></li>

                    <li class="nav-item mt-3 mb-1 text-muted small fw-bold text-uppercase px-3">Laporan</li>
                    <li class="nav-item"><a href="{{ route('laporan.index') }}" class="nav-link"><i
                                class="bi bi-file-earmark-text"></i> Laporan & Ekspor</a></li>

                @elseif($role === 'kepala_gudang')
                    <li class="nav-item"><a href="/kepala/dashboard"
                            class="nav-link {{ request()->is('kepala/dashboard') ? 'active' : '' }}"><i
                                class="bi bi-speedometer2"></i> Dashboard</a></li>

                    <li class="nav-item mt-3 mb-1 text-muted small fw-bold text-uppercase px-3">Operasional</li>
                    <li class="nav-item"><a href="{{ route('stok.catat') }}" class="nav-link"><i
                                class="bi bi-journal-plus"></i> Catat Stok</a></li>
                    <li class="nav-item"><a href="{{ route('stok.adjustment') }}" class="nav-link"><i
                                class="bi bi-sliders"></i> Koreksi Stok</a></li>
                    <li class="nav-item"><a href="{{ route('rop.index') }}" class="nav-link"><i
                                class="bi bi-graph-up-arrow"></i> ROP & Parameter</a></li>
                    <li class="nav-item"><a href="{{ route('stok.riwayat') }}" class="nav-link"><i
                                class="bi bi-clock-history"></i> Riwayat Transaksi</a></li>

                    <li class="nav-item mt-3 mb-1 text-muted small fw-bold text-uppercase px-3">Laporan</li>
                    <li class="nav-item"><a href="{{ route('laporan.index') }}" class="nav-link"><i
                                class="bi bi-file-earmark-text"></i> Laporan & Ekspor</a></li>

                @elseif($role === 'staf_gudang')
                    <li class="nav-item"><a href="/staf/dashboard"
                            class="nav-link {{ request()->is('staf/dashboard') ? 'active' : '' }}"><i
                                class="bi bi-speedometer2"></i> Dashboard</a></li>

                    <li class="nav-item mt-3 mb-1 text-muted small fw-bold text-uppercase px-3">Operasional</li>
                    <li class="nav-item"><a href="{{ route('stok.catat') }}" class="nav-link"><i
                                class="bi bi-journal-plus"></i> Catat Stok</a></li>
                    <li class="nav-item"><a href="{{ route('rop.index') }}" class="nav-link"><i
                                class="bi bi-graph-up-arrow"></i> Status ROP</a></li>
                    <li class="nav-item"><a href="{{ route('stok.riwayat') }}" class="nav-link"><i
                                class="bi bi-clock-history"></i> Riwayat Transaksi</a></li>

                @elseif($role === 'manajer_operasional')
                    <li class="nav-item"><a href="/manajer/dashboard"
                            class="nav-link {{ request()->is('manajer/dashboard') ? 'active' : '' }}"><i
                                class="bi bi-speedometer2"></i> Dashboard</a></li>

                    <li class="nav-item mt-3 mb-1 text-muted small fw-bold text-uppercase px-3">Laporan</li>
                    <li class="nav-item"><a href="{{ route('rop.index') }}" class="nav-link"><i
                                class="bi bi-graph-up-arrow"></i> Dashboard ROP</a></li>
                    <li class="nav-item"><a href="{{ route('stok.riwayat') }}" class="nav-link"><i
                                class="bi bi-clock-history"></i> Riwayat Transaksi</a></li>
                    <li class="nav-item"><a href="{{ route('laporan.index') }}" class="nav-link"><i
                                class="bi bi-file-earmark-text"></i> Laporan & Ekspor</a></li>
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
                        <div class="dropdown me-3">
                            <button class="btn btn-light position-relative" type="button" id="dropdownNotif"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-bell"></i>
                                <span
                                    class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger d-none"
                                    id="bellBadgeCount">
                                    0
                                </span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0"
                                aria-labelledby="dropdownNotif" style="width: 300px;">
                                <li>
                                    <h6 class="dropdown-header fw-bold">Notifikasi Terkini</h6>
                                </li>
                                <div id="notifDropdownList">
                                    <li><span class="dropdown-item text-center text-muted small py-3">Memuat...</span>
                                    </li>
                                </div>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li>
                                    <div class="d-flex justify-content-between px-3 pb-1">
                                        <a href="#" class="text-decoration-none small" id="markAllReadBtn">Tandai semua
                                            dibaca</a>
                                        <a href="{{ route('notifikasi.index') }}"
                                            class="text-decoration-none small fw-bold">Lihat semua</a>
                                    </div>
                                </li>
                            </ul>
                        </div>

                        <div class="me-3 text-end">
                            <div class="fw-bold">{{ session('nama') }}</div>
                            <small
                                class="badge bg-secondary">{{ str_replace('_', ' ', strtoupper(session('role'))) }}</small>
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
        <div id="liveToast" class="toast align-items-center text-white border-0" role="alert" aria-live="assertive"
            aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body" id="toastMessage"></div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                    aria-label="Close"></button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function showToast(message, type = 'success') {
            const toastEl = document.getElementById('liveToast');
            const toastMessage = document.getElementById('toastMessage');

            toastEl.classList.remove('bg-success', 'bg-danger', 'bg-warning', 'bg-info');

            if (type === 'success') toastEl.classList.add('bg-success');
            else if (type === 'error') toastEl.classList.add('bg-danger');
            else if (type === 'warning') toastEl.classList.add('bg-warning');
            else toastEl.classList.add('bg-info');

            toastMessage.textContent = message;

            const toast = new bootstrap.Toast(toastEl);
            toast.show();
        }

        // --- NOTIFIKASI AJAX POLLING ---
        document.addEventListener('DOMContentLoaded', function () {
            const badge = document.getElementById('bellBadgeCount');
            const dropdownList = document.getElementById('notifDropdownList');
            const markAllBtn = document.getElementById('markAllReadBtn');

            function fetchNotifikasi() {
                fetch('/api/notifikasi/unread-count', {
                    headers: { 'Accept': 'application/json' }
                })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            // Update badge
                            if (data.unread_count > 0) {
                                badge.textContent = data.unread_count;
                                badge.classList.remove('d-none');
                            } else {
                                badge.classList.add('d-none');
                            }

                            // Update dropdown list
                            if (data.latest.length === 0) {
                                dropdownList.innerHTML = '<li><span class="dropdown-item text-center text-muted small py-3">Belum ada notifikasi</span></li>';
                            } else {
                                let html = '';
                                data.latest.forEach(n => {
                                    let bgClass = n.status === 'belum_dibaca' ? 'bg-light' : '';
                                    let fwClass = n.status === 'belum_dibaca' ? 'fw-bold' : '';
                                    html += `
                                    <li>
                                        <a class="dropdown-item py-2 border-bottom ${bgClass}" href="#" onclick="markAsRead(${n.id})">
                                            <div class="d-flex justify-content-between mb-1">
                                                <small class="text-muted" style="font-size: 10px;"><i class="bi bi-clock"></i> ${n.waktu_relatif}</small>
                                            </div>
                                            <p class="mb-0 small text-wrap ${fwClass}" style="line-height: 1.2;">${n.pesan}</p>
                                        </a>
                                    </li>
                                `;
                                });
                                dropdownList.innerHTML = html;
                            }
                        }
                    })
                    .catch(err => console.error(err));
            }

            // Initial fetch & polling every 60s
            fetchNotifikasi();
            setInterval(fetchNotifikasi, 60000);

            // Mark all as read
            markAllBtn.addEventListener('click', function (e) {
                e.preventDefault();
                fetch('/api/notifikasi/mark-all-read', {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) fetchNotifikasi();
                    });
            });

            // Global mark as read function for single items
            window.markAsRead = function (id) {
                fetch(`/api/notifikasi/${id}/read`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            fetchNotifikasi();
                            // Also dispatch custom event in case we are on the notifikasi.index page
                            window.dispatchEvent(new Event('notifikasiUpdated'));
                        }
                    });
            }
        });
    </script>
    @stack('scripts')
</body>

</html>