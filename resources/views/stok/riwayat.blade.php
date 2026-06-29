@extends('layouts.app')
@section('title', 'Riwayat Transaksi Stok')

@section('content')
<div class="container-fluid p-4">
    <h2 class="mb-4 fw-bold">Riwayat Transaksi Stok</h2>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form id="filterForm" class="row g-3 align-items-end">
                <div class="col-md-2">
                    <label class="form-label small fw-bold">Dari Tanggal</label>
                    <input type="date" class="form-control form-control-sm" name="tanggal_dari" id="tanggal_dari">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">Sampai Tanggal</label>
                    <input type="date" class="form-control form-control-sm" name="tanggal_sampai" id="tanggal_sampai">
                </div>
                
                @if(in_array(session('role'), ['admin_gudang', 'manajer_operasional']))
                <div class="col-md-2">
                    <label class="form-label small fw-bold">Gudang</label>
                    <select class="form-select form-select-sm" name="gudang_id" id="filter_gudang">
                        <option value="">Semua Gudang</option>
                        @foreach($gudang as $g)
                            <option value="{{ $g->id }}">{{ $g->nama }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Barang</label>
                    <select class="form-select form-select-sm select-barang" name="barang_id" id="filter_barang">
                        <option value="">Semua Barang</option>
                        @foreach($barang as $b)
                            <option value="{{ $b->id }}">{{ $b->nama }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label class="form-label small fw-bold">Jenis</label>
                    <select class="form-select form-select-sm" name="jenis" id="filter_jenis">
                        <option value="">Semua Jenis</option>
                        <option value="masuk">Masuk</option>
                        <option value="keluar">Keluar</option>
                        <option value="adjustment">Adjustment</option>
                    </select>
                </div>

                <div class="col-md-1 text-end">
                    <button type="button" class="btn btn-sm btn-outline-secondary w-100" id="btnReset" title="Reset Filter"><i class="bi bi-arrow-clockwise"></i></button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Tanggal</th>
                            <th>Barang</th>
                            @if(in_array(session('role'), ['admin_gudang', 'manajer_operasional']))
                            <th>Gudang</th>
                            @endif
                            <th>Jenis</th>
                            <th class="text-end">Jumlah</th>
                            <th class="text-end">S. Sebelum</th>
                            <th class="text-end">S. Sesudah</th>
                            <th>Catatan / Supplier</th>
                            <th>Dicatat Oleh</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">Memuat data...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div class="d-flex justify-content-between align-items-center p-3 border-top">
                <div class="small text-muted" id="pageInfo">Menampilkan 0 data</div>
                <nav>
                    <ul class="pagination pagination-sm mb-0" id="pagination">
                        <!-- Pagination buttons via JS -->
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        let tsBarang = new TomSelect('#filter_barang', { create: false, sortField: {field: "text", direction: "asc"} });

        const form = document.getElementById('filterForm');
        const btnReset = document.getElementById('btnReset');
        const tableBody = document.getElementById('tableBody');
        const pagination = document.getElementById('pagination');
        const pageInfo = document.getElementById('pageInfo');
        
        let currentPage = 1;

        function loadData(page = 1) {
            currentPage = page;
            const params = new URLSearchParams(new FormData(form));
            params.append('page', page);

            tableBody.innerHTML = `<tr><td colspan="9" class="text-center py-4"><div class="spinner-border text-primary spinner-border-sm" role="status"></div> Memuat...</td></tr>`;

            fetch(`/stok/api/riwayat?${params.toString()}`)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        renderTable(data.data.data);
                        renderPagination(data.data);
                    }
                })
                .catch(err => {
                    tableBody.innerHTML = `<tr><td colspan="9" class="text-center py-4 text-danger">Gagal memuat data.</td></tr>`;
                });
        }

        function renderTable(rows) {
            if (rows.length === 0) {
                tableBody.innerHTML = `<tr><td colspan="9" class="text-center py-4 text-muted">Tidak ada riwayat transaksi.</td></tr>`;
                return;
            }

            let html = '';
            rows.forEach(row => {
                let badgeClass = 'bg-secondary';
                if (row.jenis === 'masuk') badgeClass = 'bg-success';
                if (row.jenis === 'keluar') badgeClass = 'bg-danger';
                if (row.jenis === 'adjustment') badgeClass = 'bg-warning text-dark';

                let infoExtra = row.catatan || '-';
                if (row.jenis === 'masuk' && row.supplier_nama) {
                    infoExtra += ` <br><small class="text-muted"><i class="bi bi-truck"></i> ${row.supplier_nama}</small>`;
                }

                html += `
                    <tr>
                        <td>${row.tanggal}</td>
                        <td class="fw-bold">${row.barang_nama}</td>
                        @if(in_array(session('role'), ['admin_gudang', 'manajer_operasional']))
                        <td>${row.gudang_nama}</td>
                        @endif
                        <td><span class="badge ${badgeClass}">${row.jenis.toUpperCase()}</span></td>
                        <td class="text-end fw-bold ${row.jenis === 'keluar' ? 'text-danger' : (row.jenis === 'masuk' ? 'text-success' : 'text-warning')}">${row.jumlah}</td>
                        <td class="text-end text-muted">${row.saldo_sebelum}</td>
                        <td class="text-end fw-bold">${row.saldo_sesudah}</td>
                        <td>${infoExtra}</td>
                        <td>${row.user_nama}</td>
                    </tr>
                `;
            });
            tableBody.innerHTML = html;
        }

        function renderPagination(meta) {
            pageInfo.textContent = `Menampilkan ${meta.from || 0} - ${meta.to || 0} dari total ${meta.total} transaksi`;
            
            let html = '';
            if (meta.last_page > 1) {
                // Prev
                html += `<li class="page-item ${meta.current_page === 1 ? 'disabled' : ''}"><a class="page-link" href="#" data-page="${meta.current_page - 1}">Prev</a></li>`;
                
                for(let i=1; i<=meta.last_page; i++) {
                    if (i === 1 || i === meta.last_page || (i >= meta.current_page - 2 && i <= meta.current_page + 2)) {
                        html += `<li class="page-item ${i === meta.current_page ? 'active' : ''}"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`;
                    } else if (i === meta.current_page - 3 || i === meta.current_page + 3) {
                        html += `<li class="page-item disabled"><a class="page-link" href="#">...</a></li>`;
                    }
                }
                
                // Next
                html += `<li class="page-item ${meta.current_page === meta.last_page ? 'disabled' : ''}"><a class="page-link" href="#" data-page="${meta.current_page + 1}">Next</a></li>`;
            }
            pagination.innerHTML = html;

            pagination.querySelectorAll('a.page-link').forEach(a => {
                a.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (!this.parentElement.classList.contains('disabled')) {
                        loadData(parseInt(this.getAttribute('data-page')));
                    }
                });
            });
        }

        // Event Listeners for Filters
        form.querySelectorAll('input, select').forEach(el => {
            el.addEventListener('change', () => loadData(1));
        });

        btnReset.addEventListener('click', () => {
            form.reset();
            tsBarang.clear();
            loadData(1);
        });

        // Initial Load
        loadData();
    });
</script>
@endsection
