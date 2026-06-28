@extends('layouts.app')
@section('title', 'Laporan & Ekspor')

@section('content')
<div class="container-fluid p-0">
    <h2 class="fw-bold mb-4">Pusat Laporan</h2>

    <ul class="nav nav-pills mb-4 gap-2" id="reportTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active rounded-pill px-4" id="stok-tab" data-bs-toggle="tab" data-bs-target="#stok" type="button" role="tab" onclick="currentTab='stok'">Stok Harian</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-pill px-4" id="transaksi-tab" data-bs-toggle="tab" data-bs-target="#transaksi" type="button" role="tab" onclick="currentTab='transaksi'">Riwayat Transaksi</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-pill px-4" id="rop-tab" data-bs-toggle="tab" data-bs-target="#rop" type="button" role="tab" onclick="currentTab='rop'">Status ROP</button>
        </li>
    </ul>

    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <h5 class="fw-bold mb-3"><i class="bi bi-funnel"></i> Filter Laporan</h5>
            
            <form id="filterForm">
                <div class="row g-3">
                    @if(in_array(session('role'), ['admin_gudang', 'manajer_operasional']))
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Gudang</label>
                        <select class="form-select" name="gudang_id" id="filterGudang">
                            <option value="">Semua Gudang</option>
                            @foreach($gudang_list as $g)
                                <option value="{{ $g->id }}">{{ $g->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    <!-- Filter Transaksi Only -->
                    <div class="col-md-3 filter-transaksi d-none">
                        <label class="form-label small fw-bold">Tanggal Mulai</label>
                        <input type="date" class="form-control" name="tanggal_mulai" id="filterTglMulai">
                    </div>
                    <div class="col-md-3 filter-transaksi d-none">
                        <label class="form-label small fw-bold">Tanggal Akhir</label>
                        <input type="date" class="form-control" name="tanggal_akhir" id="filterTglAkhir">
                    </div>
                    <div class="col-md-3 filter-transaksi d-none">
                        <label class="form-label small fw-bold">Jenis Transaksi</label>
                        <select class="form-select" name="jenis" id="filterJenis">
                            <option value="">Semua Jenis</option>
                            <option value="masuk">Masuk</option>
                            <option value="keluar">Keluar</option>
                            <option value="adjustment">Adjustment</option>
                        </select>
                    </div>

                    <!-- Filter Stok & ROP Only -->
                    <div class="col-md-3 filter-status">
                        <label class="form-label small fw-bold">Status Stok</label>
                        <select class="form-select" name="status" id="filterStatus">
                            <option value="">Semua Status</option>
                            <option value="aman">Aman</option>
                            <option value="menipis">Menipis</option>
                            <option value="kritis">Kritis</option>
                        </select>
                    </div>

                    <div class="col-12 mt-4 d-flex gap-2">
                        <button type="button" class="btn btn-primary px-4" id="btnPreview" onclick="generatePreview()">
                            <i class="bi bi-search"></i> Generate Preview
                        </button>
                        
                        <div class="ms-auto d-flex gap-2">
                            <button type="button" class="btn btn-outline-danger px-4" id="btnExportPdf" onclick="exportData('pdf')" disabled>
                                <i class="bi bi-file-earmark-pdf"></i> Export PDF
                            </button>
                            <button type="button" class="btn btn-outline-success px-4" id="btnExportExcel" onclick="exportData('excel')" disabled>
                                <i class="bi bi-file-earmark-excel"></i> Export Excel
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- PREVIEW AREA -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="previewTable">
                    <thead class="table-light text-muted small" id="previewHead">
                        <tr><th class="ps-4">Silakan klik 'Generate Preview' untuk melihat data.</th></tr>
                    </thead>
                    <tbody id="previewBody">
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    let currentTab = 'stok';

    // Handle Tab Changes for Filter Visibility
    document.querySelectorAll('button[data-bs-toggle="tab"]').forEach(tab => {
        tab.addEventListener('shown.bs.tab', function (event) {
            currentTab = event.target.id.replace('-tab', '');
            
            const trFilters = document.querySelectorAll('.filter-transaksi');
            const stFilters = document.querySelectorAll('.filter-status');
            
            if (currentTab === 'transaksi') {
                trFilters.forEach(el => el.classList.remove('d-none'));
                stFilters.forEach(el => el.classList.add('d-none'));
            } else {
                trFilters.forEach(el => el.classList.add('d-none'));
                stFilters.forEach(el => el.classList.remove('d-none'));
            }

            // Reset preview
            document.getElementById('previewHead').innerHTML = `<tr><th class="ps-4">Silakan klik 'Generate Preview' untuk melihat data ${currentTab}.</th></tr>`;
            document.getElementById('previewBody').innerHTML = '';
            toggleExportBtns(false);
        });
    });

    function toggleExportBtns(enabled) {
        document.getElementById('btnExportPdf').disabled = !enabled;
        document.getElementById('btnExportExcel').disabled = !enabled;
    }

    function generatePreview() {
        const btn = document.getElementById('btnPreview');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...';
        btn.disabled = true;
        toggleExportBtns(false);

        const form = document.getElementById('filterForm');
        const formData = new FormData(form);
        const params = new URLSearchParams(formData);

        fetch(`/api/laporan/${currentTab}?${params.toString()}`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    renderTable(currentTab, data.data);
                    toggleExportBtns(data.data.length > 0);
                } else {
                    showToast('Gagal memuat preview.', 'error');
                }
            })
            .catch(err => {
                console.error(err);
                showToast('Terjadi kesalahan koneksi.', 'error');
            })
            .finally(() => {
                btn.innerHTML = originalText;
                btn.disabled = false;
            });
    }

    function renderTable(jenis, data) {
        const thead = document.getElementById('previewHead');
        const tbody = document.getElementById('previewBody');
        
        let headHtml = '';
        let bodyHtml = '';

        if (data.length === 0) {
            tbody.innerHTML = `<tr><td colspan="10" class="text-center py-4 text-muted">Tidak ada data untuk filter yang dipilih.</td></tr>`;
            return;
        }

        if (jenis === 'stok') {
            headHtml = `<tr>
                <th class="ps-4">No</th>
                <th>Nama Barang</th>
                <th>Gudang</th>
                <th>Saldo</th>
                <th>ROP</th>
                <th>Status</th>
            </tr>`;
            data.forEach((r, i) => {
                bodyHtml += `<tr>
                    <td class="ps-4">${i+1}</td>
                    <td>${r.barang_nama}</td>
                    <td>${r.gudang_nama}</td>
                    <td>${r.saldo}</td>
                    <td>${r.rop ?? '-'}</td>
                    <td><span class="badge bg-secondary">${r.status.toUpperCase().replace('_', ' ')}</span></td>
                </tr>`;
            });
        } 
        else if (jenis === 'transaksi') {
            headHtml = `<tr>
                <th class="ps-4">No</th>
                <th>Tanggal</th>
                <th>Barang</th>
                <th>Gudang</th>
                <th>Jenis</th>
                <th>Jumlah</th>
                <th>Sld Sebelum</th>
                <th>Sld Sesudah</th>
                <th>Supplier</th>
                <th>User</th>
            </tr>`;
            data.forEach((r, i) => {
                bodyHtml += `<tr>
                    <td class="ps-4">${i+1}</td>
                    <td>${r.tanggal}</td>
                    <td>${r.barang_nama}</td>
                    <td>${r.gudang_nama}</td>
                    <td>${r.jenis.toUpperCase()}</td>
                    <td>${r.jumlah}</td>
                    <td>${r.saldo_sebelum}</td>
                    <td>${r.saldo_sesudah}</td>
                    <td>${r.supplier_nama ?? '-'}</td>
                    <td>${r.user_nama}</td>
                </tr>`;
            });
        }
        else if (jenis === 'rop') {
            headHtml = `<tr>
                <th class="ps-4">No</th>
                <th>Barang</th>
                <th>Gudang</th>
                <th>ADU</th>
                <th>Lead Time</th>
                <th>Safety Stock</th>
                <th>ROP</th>
                <th>Stok Aktual</th>
                <th>Status</th>
            </tr>`;
            data.forEach((r, i) => {
                bodyHtml += `<tr>
                    <td class="ps-4">${i+1}</td>
                    <td>${r.barang_nama}</td>
                    <td>${r.gudang_nama}</td>
                    <td>${r.adu}</td>
                    <td>${r.lead_time}</td>
                    <td>${r.safety_stock}</td>
                    <td>${r.rop}</td>
                    <td>${r.stok_aktual}</td>
                    <td><span class="badge bg-secondary">${r.status.toUpperCase().replace('_', ' ')}</span></td>
                </tr>`;
            });
        }

        thead.innerHTML = headHtml;
        tbody.innerHTML = bodyHtml;
    }

    function exportData(format) {
        const form = document.getElementById('filterForm');
        const formData = new FormData(form);
        const params = new URLSearchParams(formData);
        
        const url = `/laporan/${currentTab}/export/${format}?${params.toString()}`;
        
        // Open download in new window/tab
        window.open(url, '_blank');
    }
</script>
@endpush
