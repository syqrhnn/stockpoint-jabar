@extends('layouts.app')
@section('title', 'Dashboard Monitoring')

@section('content')
<div class="container-fluid p-0">
    
    <!-- Top Filter -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0">Overview</h2>
        <div class="d-flex gap-2 align-items-center">
            @if(in_array(session('role'), ['admin_gudang', 'manajer_operasional']))
            <select class="form-select" id="filterGudang" style="width: 250px;">
                <option value="">Semua Gudang</option>
                @foreach($gudang_list as $g)
                    <option value="{{ $g->id }}">{{ $g->nama }}</option>
                @endforeach
            </select>
            @endif
            <button class="btn btn-primary shadow-sm" id="btnRefresh" title="Refresh Sekarang">
                <i class="bi bi-arrow-clockwise" id="iconRefresh"></i>
            </button>
            <span class="text-muted small ms-2 d-none d-md-block" id="lastUpdated">Diperbarui: -</span>
        </div>
    </div>

    <!-- SECTION 1: Summary Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-primary text-white">
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-white-50 fw-bold mb-1">TOTAL SKU Dikelola</div>
                            <h2 class="display-5 fw-bold mb-0" id="card_sku">0</h2>
                        </div>
                        <div class="bg-white bg-opacity-25 p-3 rounded-circle">
                            <i class="bi bi-box fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-danger fw-bold mb-1">Stok Kritis</div>
                            <h2 class="display-5 fw-bold mb-0" id="card_kritis">0</h2>
                        </div>
                        <div class="bg-danger bg-opacity-10 text-danger p-3 rounded-circle">
                            <i class="bi bi-exclamation-triangle-fill fs-3"></i>
                        </div>
                    </div>
                    <div class="mt-3 text-muted small fw-bold">
                        <span class="text-danger" id="card_pct_kritis">0%</span> dari total SKU
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-warning fw-bold mb-1">Stok Menipis</div>
                            <h2 class="display-5 fw-bold mb-0" id="card_menipis">0</h2>
                        </div>
                        <div class="bg-warning bg-opacity-10 text-warning p-3 rounded-circle">
                            <i class="bi bi-exclamation-circle-fill fs-3"></i>
                        </div>
                    </div>
                    <div class="mt-3 text-muted small fw-bold">
                        <span class="text-warning" id="card_pct_menipis">0%</span> dari total SKU
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-success fw-bold mb-1">Gudang Aktif</div>
                            <h2 class="display-5 fw-bold mb-0" id="card_gudang">0</h2>
                        </div>
                        <div class="bg-success bg-opacity-10 text-success p-3 rounded-circle">
                            <i class="bi bi-building fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <!-- SECTION 2: Priority Table -->
        <div class="col-md-8">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-0 pt-4 pb-0 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0">Prioritas Restock</h5>
                    <select class="form-select form-select-sm w-auto" id="filterStatusTable">
                        <option value="">Semua Status (Kritis & Menipis)</option>
                        <option value="kritis">Kritis Saja</option>
                        <option value="menipis">Menipis Saja</option>
                    </select>
                </div>
                <div class="card-body px-4 py-3">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light text-muted small">
                                <tr>
                                    <th>Barang</th>
                                    <th>Gudang</th>
                                    <th class="text-center">Aktual / ROP</th>
                                    <th class="text-center">Status</th>
                                    @if(in_array(session('role'), ['admin_gudang', 'kepala_gudang', 'staf_gudang']))
                                    <th class="text-end">Aksi</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody id="tablePriority">
                                <tr><td colspan="5" class="text-center text-muted">Memuat...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- SECTION 5: Notifications -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-0 pt-4 pb-0 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0">Notifikasi Terkini</h5>
                    <span class="badge bg-danger rounded-pill" id="badgeNotifCount">0 Baru</span>
                </div>
                <div class="card-body px-4 py-3">
                    <div class="list-group list-group-flush" id="notifList">
                        <div class="text-center py-4 text-muted small">Memuat...</div>
                    </div>
                    <div class="mt-3 text-center">
                        <a href="#" class="text-decoration-none small fw-bold">Lihat Semua</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SECTION 4: Chart -->
    <div class="row g-4 mb-4">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4">Pergerakan Stok (7 Hari Terakhir)</h5>
                    <div style="height: 300px;">
                        <canvas id="stokChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- SECTION 3: Per Gudang -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-light">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4">Status Per Gudang</h5>
                    <div id="gudangSummaryList" class="d-flex flex-column gap-3">
                        <div class="text-center py-4 text-muted small">Memuat...</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    let stokChartInstance = null;

    const filterGudang = document.getElementById('filterGudang');
    const filterStatus = document.getElementById('filterStatusTable');
    const btnRefresh = document.getElementById('btnRefresh');
    const iconRefresh = document.getElementById('iconRefresh');
    
    function initChart() {
        const ctx = document.getElementById('stokChart').getContext('2d');
        stokChartInstance = new Chart(ctx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [
                    {
                        label: 'Barang Masuk',
                        data: [],
                        borderColor: '#198754',
                        backgroundColor: 'rgba(25, 135, 84, 0.1)',
                        borderWidth: 2,
                        tension: 0.3,
                        fill: true
                    },
                    {
                        label: 'Barang Keluar',
                        data: [],
                        borderColor: '#dc3545',
                        backgroundColor: 'rgba(220, 53, 69, 0.1)',
                        borderWidth: 2,
                        tension: 0.3,
                        fill: true
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top' }
                },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    }

    function fetchDashboardData() {
        iconRefresh.classList.add('bi-spin'); // if we have spin css, or just standard
        iconRefresh.style.animation = "spin 1s infinite linear";

        let url = '/api/dashboard/summary?1=1';
        if (filterGudang && filterGudang.value) {
            url += '&gudang_id=' + filterGudang.value;
        }
        if (filterStatus && filterStatus.value) {
            url += '&status=' + filterStatus.value;
        }

        fetch(url)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    updateCards(data.cards);
                    updateTable(data.priority);
                    updateGudangSummary(data.gudang_summary);
                    updateChart(data.chart);
                    updateNotif(data.notifikasi, data.unread_notif);
                    
                    const now = new Date();
                    document.getElementById('lastUpdated').textContent = 'Diperbarui: ' + now.toLocaleTimeString();
                }
            })
            .catch(err => console.error(err))
            .finally(() => {
                iconRefresh.style.animation = "none";
            });
    }

    function updateCards(c) {
        document.getElementById('card_sku').textContent = c.total_sku;
        document.getElementById('card_kritis').textContent = c.kritis;
        document.getElementById('card_pct_kritis').textContent = c.pct_kritis + '%';
        document.getElementById('card_menipis').textContent = c.menipis;
        document.getElementById('card_pct_menipis').textContent = c.pct_menipis + '%';
        document.getElementById('card_gudang').textContent = c.gudang_aktif;
    }

    function updateTable(rows) {
        const tbody = document.getElementById('tablePriority');
        if (rows.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-muted">Aman. Tidak ada barang prioritas restock.</td></tr>';
            return;
        }

        let html = '';
        rows.forEach(r => {
            let badge = r.status === 'kritis' ? 'bg-danger' : 'bg-warning text-dark';
            let ropText = r.rop !== null ? r.rop : '-';
            html += `
                <tr>
                    <td class="fw-bold">${r.barang_nama}</td>
                    <td>${r.gudang_nama}</td>
                    <td class="text-center fw-bold fs-6">
                        <span class="${r.status === 'kritis' ? 'text-danger' : 'text-warning'}">${r.saldo}</span> 
                        <span class="text-muted small">/ ${ropText}</span>
                    </td>
                    <td class="text-center"><span class="badge ${badge}">${r.status.toUpperCase()}</span></td>
                    @if(in_array(session('role'), ['admin_gudang', 'kepala_gudang', 'staf_gudang']))
                    <td class="text-end">
                        <a href="{{ route('stok.catat') }}" class="btn btn-sm btn-outline-primary fw-bold" title="Catat Masuk"><i class="bi bi-plus-lg"></i> Restock</a>
                    </td>
                    @endif
                </tr>
            `;
        });
        tbody.innerHTML = html;
    }

    function updateGudangSummary(summaryObj) {
        const container = document.getElementById('gudangSummaryList');
        const keys = Object.keys(summaryObj);
        if (keys.length === 0) {
            container.innerHTML = '<div class="text-center text-muted">Tidak ada data</div>';
            return;
        }

        let html = '';
        keys.forEach(k => {
            const v = summaryObj[k];
            html += `
                <div class="bg-white p-3 rounded shadow-sm border">
                    <div class="fw-bold mb-2">${k} <span class="badge bg-secondary float-end">${v.total} item</span></div>
                    <div class="d-flex justify-content-between text-center small mt-3">
                        <div>
                            <div class="text-danger fw-bold fs-5">${v.kritis || 0}</div>
                            <div class="text-muted">Kritis</div>
                        </div>
                        <div>
                            <div class="text-warning fw-bold fs-5">${v.menipis || 0}</div>
                            <div class="text-muted">Menipis</div>
                        </div>
                        <div>
                            <div class="text-success fw-bold fs-5">${v.aman || 0}</div>
                            <div class="text-muted">Aman</div>
                        </div>
                    </div>
                </div>
            `;
        });
        container.innerHTML = html;
    }

    function updateChart(chartData) {
        stokChartInstance.data.labels = chartData.labels;
        stokChartInstance.data.datasets[0].data = chartData.masuk;
        stokChartInstance.data.datasets[1].data = chartData.keluar;
        stokChartInstance.update();
    }

    function updateNotif(notif, unreadCount) {
        document.getElementById('badgeNotifCount').textContent = unreadCount + ' Baru';
        const list = document.getElementById('notifList');

        if (notif.length === 0) {
            list.innerHTML = '<div class="text-center py-4 text-muted small">Belum ada notifikasi</div>';
            return;
        }

        let html = '';
        notif.forEach(n => {
            let bgClass = n.is_read ? 'bg-white' : 'bg-light';
            let dot = n.is_read ? '' : '<span class="p-1 bg-danger border border-light rounded-circle position-absolute top-50 start-0 translate-middle"></span>';
            html += `
                <a href="${n.link || '#'}" class="list-group-item list-group-item-action py-3 ${bgClass} position-relative border-start-0 border-end-0">
                    ${dot}
                    <div class="d-flex w-100 justify-content-between align-items-center mb-1">
                        <h6 class="mb-0 fw-bold ${n.is_read ? 'text-muted' : ''}">${n.judul}</h6>
                        <small class="text-muted" style="font-size: 11px;">${new Date(n.created_at).toLocaleDateString()}</small>
                    </div>
                    <p class="mb-0 small text-muted text-truncate" title="${n.pesan}">${n.pesan}</p>
                </a>
            `;
        });
        list.innerHTML = html;
    }

    // Styles for spin animation
    const style = document.createElement('style');
    style.innerHTML = `
        @keyframes spin { 100% { transform: rotate(360deg); } }
    `;
    document.head.appendChild(style);

    // Initialization
    initChart();
    fetchDashboardData();

    // Event Listeners
    if (filterGudang) filterGudang.addEventListener('change', fetchDashboardData);
    if (filterStatus) filterStatus.addEventListener('change', fetchDashboardData);
    btnRefresh.addEventListener('click', fetchDashboardData);

    // Auto refresh every 5 mins (300,000 ms)
    setInterval(fetchDashboardData, 300000);
});
</script>
@endpush
