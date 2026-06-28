@extends('layouts.app')
@section('title', 'Reorder Point (ROP) Dashboard')

@section('content')
<div class="container-fluid p-4">
    <h2 class="mb-4 fw-bold">Reorder Point (ROP) Dashboard</h2>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form id="filterForm" class="row g-3 align-items-end">
                @if(in_array(session('role'), ['admin_gudang', 'manajer_operasional']))
                <div class="col-md-3">
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
                    <label class="form-label small fw-bold">Status Stok</label>
                    <select class="form-select form-select-sm" name="status" id="filter_status">
                        <option value="">Semua Status</option>
                        <option value="kritis">Kritis</option>
                        <option value="menipis">Menipis</option>
                        <option value="aman">Aman</option>
                        <option value="belum_dikonfigurasi">Belum Dikonfigurasi</option>
                    </select>
                </div>

                <div class="col-md-1">
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
                            <th>Barang</th>
                            @if(in_array(session('role'), ['admin_gudang', 'manajer_operasional']))
                            <th>Gudang</th>
                            @endif
                            <th class="text-end">ADU (unit/hari)</th>
                            <th class="text-end">Lead Time</th>
                            <th class="text-end">Safety Stock</th>
                            <th class="text-end text-primary">ROP</th>
                            <th class="text-end fw-bold">Stok Aktual</th>
                            <th class="text-center">Status</th>
                            @if(in_array(session('role'), ['admin_gudang', 'kepala_gudang']))
                            <th class="text-center">Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        <tr>
                            <td colspan="{{ in_array(session('role'), ['admin_gudang', 'manajer_operasional']) ? 9 : 8 }}" class="text-center py-4 text-muted">Memuat data...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Konfigurasi -->
<div class="modal fade" id="modalKonfigurasi" tabindex="-1" aria-labelledby="modalKonfigurasiLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-bottom-0 pb-0">
        <h5 class="modal-title fw-bold" id="modalKonfigurasiLabel"><i class="bi bi-sliders text-primary"></i> Konfigurasi ROP</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="formRop" class="needs-validation" novalidate>
          <div class="modal-body pt-3">
              <input type="hidden" id="barang_id" name="barang_id">
              <input type="hidden" id="gudang_id" name="gudang_id">
              
              <div class="alert alert-secondary py-2 small mb-3">
                  <span id="labelBarang" class="fw-bold"></span> &bull; <span id="labelGudang"></span>
              </div>

              <div class="mb-3">
                  <label class="form-label fw-bold d-flex justify-content-between">
                      <span>ADU Terkini</span>
                      <i class="bi bi-question-circle text-muted" data-bs-toggle="tooltip" title="ADU (Average Daily Usage) dihitung otomatis dari total keluarnya barang selama 30 hari terakhir dibagi 30."></i>
                  </label>
                  <div class="input-group">
                      <input type="text" class="form-control bg-light" id="input_adu" readonly>
                      <span class="input-group-text bg-light text-muted">unit/hari</span>
                  </div>
              </div>

              <div class="row">
                  <div class="col-md-6 mb-3">
                      <label class="form-label fw-bold">Lead Time <span class="text-danger">*</span></label>
                      <div class="input-group">
                          <input type="number" class="form-control param-input" id="input_lead_time" name="lead_time" min="1" required>
                          <span class="input-group-text">hari</span>
                      </div>
                  </div>
                  <div class="col-md-6 mb-3">
                      <label class="form-label fw-bold">Safety Stock <span class="text-danger">*</span></label>
                      <div class="input-group">
                          <input type="number" class="form-control param-input" id="input_safety_stock" name="safety_stock" min="0" required>
                          <span class="input-group-text">unit</span>
                      </div>
                  </div>
              </div>

              <div class="p-3 bg-light rounded text-center border">
                  <div class="small text-muted text-uppercase fw-bold mb-1">Preview Real-time ROP</div>
                  <div class="fs-2 fw-bold text-primary" id="previewRop">0.00</div>
                  <div class="small text-muted">Rumus: (ADU &times; Lead Time) + Safety Stock</div>
              </div>
          </div>
          <div class="modal-footer border-top-0 pt-0">
            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-primary fw-bold" id="btnSimpanRop">Simpan Parameter</button>
          </div>
      </form>
    </div>
  </div>
</div>

<!-- Toast Container -->
<div class="toast-container position-fixed bottom-0 end-0 p-3">
    <div id="actionToast" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body" id="toastMessage">Berhasil.</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Init Tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
          return new bootstrap.Tooltip(tooltipTriggerEl)
        })

        const formFilter = document.getElementById('filterForm');
        const btnReset = document.getElementById('btnReset');
        const tableBody = document.getElementById('tableBody');
        const actionToast = new bootstrap.Toast(document.getElementById('actionToast'));
        const modalInstance = new bootstrap.Modal(document.getElementById('modalKonfigurasi'));
        
        let loadedData = []; // Untuk cache baris tabel

        function showToast(message, isSuccess = true) {
            document.getElementById('toastMessage').textContent = message;
            const tEl = document.getElementById('actionToast');
            tEl.className = `toast align-items-center border-0 text-bg-${isSuccess ? 'success' : 'danger'}`;
            actionToast.show();
        }

        function loadData() {
            const params = new URLSearchParams(new FormData(formFilter));
            const cols = {{ in_array(session('role'), ['admin_gudang', 'manajer_operasional']) ? 9 : 8 }};
            tableBody.innerHTML = `<tr><td colspan="${cols}" class="text-center py-4"><div class="spinner-border text-primary spinner-border-sm" role="status"></div> Memuat...</td></tr>`;

            fetch(`/rop/api/data?${params.toString()}`)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        loadedData = data.data;
                        renderTable(loadedData);
                    }
                })
                .catch(err => {
                    tableBody.innerHTML = `<tr><td colspan="${cols}" class="text-center py-4 text-danger">Gagal memuat data.</td></tr>`;
                });
        }

        function renderTable(rows) {
            if (rows.length === 0) {
                const cols = {{ in_array(session('role'), ['admin_gudang', 'manajer_operasional']) ? 9 : 8 }};
                tableBody.innerHTML = `<tr><td colspan="${cols}" class="text-center py-4 text-muted">Tidak ada data.</td></tr>`;
                return;
            }

            let html = '';
            rows.forEach((row, index) => {
                let badgeClass = 'bg-secondary';
                if (row.status === 'aman') badgeClass = 'bg-success';
                if (row.status === 'menipis') badgeClass = 'bg-warning text-dark';
                if (row.status === 'kritis') badgeClass = 'bg-danger';

                let txtAdu = row.adu !== null ? row.adu : '-';
                let txtLt = row.lead_time !== null ? row.lead_time : '-';
                let txtSs = row.safety_stock !== null ? row.safety_stock : '-';
                let txtRop = row.rop !== null ? row.rop : '-';

                html += `
                    <tr>
                        <td class="fw-bold">${row.barang_nama} <small class="text-muted">(${row.satuan})</small></td>
                        @if(in_array(session('role'), ['admin_gudang', 'manajer_operasional']))
                        <td>${row.gudang_nama}</td>
                        @endif
                        <td class="text-end">${txtAdu}</td>
                        <td class="text-end">${txtLt}</td>
                        <td class="text-end">${txtSs}</td>
                        <td class="text-end fw-bold text-primary">${txtRop}</td>
                        <td class="text-end fw-bold fs-5">${row.saldo_aktual}</td>
                        <td class="text-center"><span class="badge ${badgeClass}">${row.status.toUpperCase().replace('_', ' ')}</span></td>
                        @if(in_array(session('role'), ['admin_gudang', 'kepala_gudang']))
                        <td class="text-center">
                            <button class="btn btn-sm btn-outline-primary btn-config" data-idx="${index}">
                                <i class="bi bi-gear"></i> Konfigurasi
                            </button>
                        </td>
                        @endif
                    </tr>
                `;
            });
            tableBody.innerHTML = html;

            document.querySelectorAll('.btn-config').forEach(btn => {
                btn.addEventListener('click', function() {
                    openModal(this.getAttribute('data-idx'));
                });
            });
        }

        // Event Listeners Filter
        formFilter.querySelectorAll('select').forEach(el => {
            el.addEventListener('change', loadData);
        });

        btnReset.addEventListener('click', () => {
            formFilter.reset();
            loadData();
        });

        // MODAL LOGIC
        const iAdu = document.getElementById('input_adu');
        const iLead = document.getElementById('input_lead_time');
        const iSafety = document.getElementById('input_safety_stock');
        const pRop = document.getElementById('previewRop');

        function openModal(index) {
            let row = loadedData[index];
            document.getElementById('barang_id').value = row.barang_id;
            document.getElementById('gudang_id').value = row.gudang_id;
            document.getElementById('labelBarang').textContent = row.barang_nama;
            document.getElementById('labelGudang').textContent = row.gudang_nama;

            // Jika ADU null, asumsikan 0 untuk kalkulasi preview. Seharusnya backend return 0 tapi ini guard clause
            let aduVal = row.adu !== null ? parseFloat(row.adu) : 0;
            iAdu.value = aduVal;
            
            iLead.value = row.lead_time !== null ? row.lead_time : '';
            iSafety.value = row.safety_stock !== null ? row.safety_stock : '';
            
            calculatePreview();
            
            document.getElementById('formRop').classList.remove('was-validated');
            modalInstance.show();
        }

        function calculatePreview() {
            let a = parseFloat(iAdu.value) || 0;
            let l = parseInt(iLead.value) || 0;
            let s = parseInt(iSafety.value) || 0;
            let res = (a * l) + s;
            pRop.textContent = res.toFixed(2);
        }

        document.querySelectorAll('.param-input').forEach(el => {
            el.addEventListener('input', calculatePreview);
        });

        // FORM SUBMIT
        const formRop = document.getElementById('formRop');
        const btnSimpan = document.getElementById('btnSimpanRop');

        formRop.addEventListener('submit', function(e) {
            e.preventDefault();
            if (!formRop.checkValidity()) {
                formRop.classList.add('was-validated');
                return;
            }

            btnSimpan.disabled = true;
            btnSimpan.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Menyimpan...';

            const formData = new FormData(formRop);
            const data = Object.fromEntries(formData.entries());

            fetch('/rop/api/konfigurasi', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(data)
            })
            .then(res => res.json())
            .then(res => {
                if (res.success) {
                    showToast(res.message);
                    modalInstance.hide();
                    loadData(); // Refresh table
                } else {
                    showToast(res.message, false);
                }
            })
            .catch(err => {
                showToast('Terjadi kesalahan koneksi.', false);
            })
            .finally(() => {
                btnSimpan.disabled = false;
                btnSimpan.textContent = 'Simpan Parameter';
            });
        });

        // Init
        loadData();
    });
</script>
@endsection
