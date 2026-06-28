@extends('layouts.app')
@section('title', 'Opening Balance')

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Opening Balance — Saldo Awal</h2>
            <p class="text-muted mb-0">Input saldo awal untuk semua barang di setiap gudang sebelum sistem Go-Live.</p>
        </div>
    </div>

    <!-- Progress Bar -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="fw-bold" id="progressLabel">Memuat data...</span>
                <span class="badge bg-primary rounded-pill" id="progressBadge">0/0</span>
            </div>
            <div class="progress" style="height: 10px;">
                <div class="progress-bar bg-success" role="progressbar" style="width: 0%" id="progressBar"></div>
            </div>
        </div>
    </div>

    <!-- Filter -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Filter Gudang</label>
                    <select class="form-select" id="filterGudang">
                        <option value="">Semua Gudang</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Filter Status</label>
                    <select class="form-select" id="filterOBStatus">
                        <option value="">Semua</option>
                        <option value="belum">Belum Diinput</option>
                        <option value="sudah">Sudah Diinput</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-muted small">
                        <tr>
                            <th class="ps-4">Barang</th>
                            <th>Gudang</th>
                            <th class="text-center">Saldo Saat Ini</th>
                            <th class="text-center" style="width: 180px;">Saldo Awal (Input)</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody id="obTableBody">
                        <tr><td colspan="5" class="text-center py-4">Memuat...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Save Button -->
    <div class="mt-4 text-end">
        <button class="btn btn-primary btn-lg px-5" id="btnSaveOB" disabled>
            <i class="bi bi-save"></i> Simpan Opening Balance
        </button>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let allItems = [];
    const tbody = document.getElementById('obTableBody');
    const filterGudang = document.getElementById('filterGudang');
    const filterStatus = document.getElementById('filterOBStatus');
    const btnSave = document.getElementById('btnSaveOB');

    function loadData() {
        fetch('/stok/api/opening-balance')
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    allItems = data.items;
                    updateProgress(data.done, data.total);
                    populateGudangFilter();
                    renderTable();
                    btnSave.disabled = false;
                }
            });
    }

    function updateProgress(done, total) {
        const pct = total > 0 ? Math.round((done / total) * 100) : 0;
        document.getElementById('progressLabel').textContent = `${done} dari ${total} barang sudah punya saldo awal`;
        document.getElementById('progressBadge').textContent = `${done}/${total}`;
        document.getElementById('progressBar').style.width = `${pct}%`;
    }

    function populateGudangFilter() {
        const gudangs = [...new Set(allItems.map(i => JSON.stringify({ id: i.gudang_id, nama: i.gudang_nama })))].map(s => JSON.parse(s));
        gudangs.forEach(g => {
            if (!filterGudang.querySelector(`option[value="${g.id}"]`)) {
                filterGudang.innerHTML += `<option value="${g.id}">${g.nama}</option>`;
            }
        });
    }

    function renderTable() {
        let filtered = allItems;
        if (filterGudang.value) filtered = filtered.filter(i => i.gudang_id == filterGudang.value);
        if (filterStatus.value === 'belum') filtered = filtered.filter(i => !i.sudah_input);
        if (filterStatus.value === 'sudah') filtered = filtered.filter(i => i.sudah_input);

        if (filtered.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-muted">Tidak ada data sesuai filter.</td></tr>';
            return;
        }

        let html = '';
        filtered.forEach(item => {
            let statusBadge = item.sudah_input
                ? '<span class="badge bg-success rounded-pill">Sudah</span>'
                : '<span class="badge bg-warning text-dark rounded-pill">Belum</span>';

            let inputField = item.sudah_input
                ? `<span class="text-muted">—</span>`
                : `<input type="number" class="form-control form-control-sm text-center ob-input"
                    data-barang="${item.barang_id}" data-gudang="${item.gudang_id}"
                    min="0" value="" placeholder="0">`;

            html += `<tr>
                <td class="ps-4 fw-bold">${item.barang_nama}</td>
                <td>${item.gudang_nama}</td>
                <td class="text-center">${item.saldo}</td>
                <td class="text-center">${inputField}</td>
                <td class="text-center">${statusBadge}</td>
            </tr>`;
        });
        tbody.innerHTML = html;
    }

    filterGudang.addEventListener('change', renderTable);
    filterStatus.addEventListener('change', renderTable);

    btnSave.addEventListener('click', function() {
        const inputs = document.querySelectorAll('.ob-input');
        const entries = [];
        inputs.forEach(input => {
            const val = parseInt(input.value);
            if (val > 0) {
                entries.push({
                    barang_id: parseInt(input.dataset.barang),
                    gudang_id: parseInt(input.dataset.gudang),
                    jumlah: val,
                });
            }
        });

        if (entries.length === 0) {
            showToast('Tidak ada data yang diisi. Masukkan minimal 1 saldo awal.', 'warning');
            return;
        }

        btnSave.disabled = true;
        btnSave.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Menyimpan...';

        fetch('/stok/api/opening-balance', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ entries })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showToast(`Berhasil menyimpan ${data.saved} saldo awal.`, 'success');
                if (data.errors.length > 0) {
                    data.errors.forEach(e => showToast(e, 'warning'));
                }
                loadData();
            } else {
                showToast(data.message || 'Gagal menyimpan.', 'error');
            }
        })
        .catch(err => showToast('Terjadi kesalahan koneksi.', 'error'))
        .finally(() => {
            btnSave.disabled = false;
            btnSave.innerHTML = '<i class="bi bi-save"></i> Simpan Opening Balance';
        });
    });

    loadData();
});
</script>
@endpush
