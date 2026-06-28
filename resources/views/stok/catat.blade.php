@extends('layouts.app')
@section('title', 'Pencatatan Stok')

@section('content')
<div class="container-fluid p-4">
    <h2 class="mb-4 fw-bold">Pencatatan Stok</h2>

    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white pb-0">
                    <ul class="nav nav-tabs border-bottom-0" id="stokTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active fw-bold text-success" id="masuk-tab" data-bs-toggle="tab" data-bs-target="#masuk" type="button" role="tab"><i class="bi bi-box-arrow-in-right"></i> Stok Masuk</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link fw-bold text-danger" id="keluar-tab" data-bs-toggle="tab" data-bs-target="#keluar" type="button" role="tab"><i class="bi bi-box-arrow-right"></i> Stok Keluar</button>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="stokTabsContent">
                        
                        <!-- Form Stok Masuk -->
                        <div class="tab-pane fade show active" id="masuk" role="tabpanel">
                            <form id="formMasuk" class="needs-validation" novalidate>
                                @csrf
                                @if(session('role') === 'admin_gudang')
                                <div class="mb-3">
                                    <label class="form-label">Gudang <span class="text-danger">*</span></label>
                                    <select class="form-select" name="gudang_id" required>
                                        <option value="">Pilih Gudang...</option>
                                        @foreach($gudang as $g)
                                            <option value="{{ $g->id }}">{{ $g->nama }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @else
                                    <input type="hidden" name="gudang_id" value="{{ session('gudang_id') }}">
                                @endif

                                <div class="mb-3">
                                    <label class="form-label">Barang <span class="text-danger">*</span></label>
                                    <select class="form-select select-barang" name="barang_id" required>
                                        <option value="">Pilih Barang...</option>
                                        @foreach($barang as $b)
                                            <option value="{{ $b->id }}">{{ $b->nama }} ({{ $b->satuan }})</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Jumlah <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" name="jumlah" min="1" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" name="tanggal" max="{{ date('Y-m-d') }}" value="{{ date('Y-m-d') }}" required>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Supplier (Opsional)</label>
                                    <select class="form-select select-supplier" name="supplier_id">
                                        <option value="">Pilih Supplier...</option>
                                        @foreach($supplier as $s)
                                            <option value="{{ $s->id }}">{{ $s->nama }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label">Catatan (Opsional)</label>
                                    <textarea class="form-control" name="catatan" rows="3" placeholder="Contoh: Batch pengiriman ke-1"></textarea>
                                </div>

                                <button type="submit" class="btn btn-success w-100 fw-bold py-2"><i class="bi bi-save"></i> Simpan Stok Masuk</button>
                            </form>
                        </div>

                        <!-- Form Stok Keluar -->
                        <div class="tab-pane fade" id="keluar" role="tabpanel">
                            <form id="formKeluar" class="needs-validation" novalidate>
                                @csrf
                                @if(session('role') === 'admin_gudang')
                                <div class="mb-3">
                                    <label class="form-label">Gudang <span class="text-danger">*</span></label>
                                    <select class="form-select" name="gudang_id" id="keluar_gudang_id" required>
                                        <option value="">Pilih Gudang...</option>
                                        @foreach($gudang as $g)
                                            <option value="{{ $g->id }}">{{ $g->nama }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @else
                                    <input type="hidden" name="gudang_id" id="keluar_gudang_id" value="{{ session('gudang_id') }}">
                                @endif

                                <div class="mb-3">
                                    <label class="form-label">Barang <span class="text-danger">*</span></label>
                                    <select class="form-select select-barang" name="barang_id" id="keluar_barang_id" required>
                                        <option value="">Pilih Barang...</option>
                                        @foreach($barang as $b)
                                            <option value="{{ $b->id }}">{{ $b->nama }} ({{ $b->satuan }})</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="alert alert-info py-2 d-none" id="infoSaldo">
                                    <i class="bi bi-info-circle-fill"></i> Saldo Tersedia: <strong id="txtSaldo">0</strong>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Jumlah <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" name="jumlah" id="keluar_jumlah" min="1" required>
                                        <div class="text-danger mt-1 small d-none" id="warningJumlah"><i class="bi bi-exclamation-triangle"></i> Jumlah melebihi saldo tersedia.</div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" name="tanggal" max="{{ date('Y-m-d') }}" value="{{ date('Y-m-d') }}" required>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label">Catatan (Opsional)</label>
                                    <textarea class="form-control" name="catatan" rows="3" placeholder="Alasan pengeluaran / referensi"></textarea>
                                </div>

                                <button type="submit" class="btn btn-danger w-100 fw-bold py-2" id="btnSubmitKeluar"><i class="bi bi-save"></i> Simpan Stok Keluar</button>
                            </form>
                        </div>
                        
                    </div>
                </div>
            </div>
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

<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Init TomSelect for searchable dropdowns
        document.querySelectorAll('.select-barang, .select-supplier').forEach(el => {
            new TomSelect(el, { create: false, sortField: {field: "text", direction: "asc"} });
        });
        @if(session('role') === 'admin_gudang')
        document.querySelectorAll('select[name="gudang_id"]').forEach(el => {
            new TomSelect(el, { create: false });
        });
        @endif

        const toastEl = document.getElementById('actionToast');
        const toast = new bootstrap.Toast(toastEl);
        function showToast(message, isSuccess = true) {
            document.getElementById('toastMessage').textContent = message;
            toastEl.className = `toast align-items-center border-0 text-bg-${isSuccess ? 'success' : 'danger'}`;
            toast.show();
        }

        // --- STOK KELUAR: Fetch Saldo Realtime ---
        const kGudang = document.getElementById('keluar_gudang_id');
        const kBarang = document.getElementById('keluar_barang_id');
        const kJumlah = document.getElementById('keluar_jumlah');
        const infoSaldo = document.getElementById('infoSaldo');
        const txtSaldo = document.getElementById('txtSaldo');
        const warningJumlah = document.getElementById('warningJumlah');
        const btnSubmitKeluar = document.getElementById('btnSubmitKeluar');

        let currentSaldo = 0;

        function checkSaldo() {
            let gid = kGudang.value;
            let bid = kBarang.value;

            if (gid && bid) {
                fetch(`/api/stok/saldo?gudang_id=${gid}&barang_id=${bid}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            currentSaldo = data.saldo;
                            txtSaldo.textContent = currentSaldo;
                            infoSaldo.classList.remove('d-none');
                            validateJumlahKeluar();
                        }
                    });
            } else {
                infoSaldo.classList.add('d-none');
                currentSaldo = 0;
            }
        }

        function validateJumlahKeluar() {
            let j = parseInt(kJumlah.value) || 0;
            if (j > currentSaldo && kBarang.value && kGudang.value) {
                warningJumlah.classList.remove('d-none');
                kJumlah.classList.add('is-invalid');
            } else {
                warningJumlah.classList.add('d-none');
                kJumlah.classList.remove('is-invalid');
            }
        }

        if(kGudang.tagName === 'SELECT') { kGudang.addEventListener('change', checkSaldo); }
        kBarang.addEventListener('change', checkSaldo);
        kJumlah.addEventListener('input', validateJumlahKeluar);

        // --- SUBMIT HANDLING ---
        function submitForm(formId, endpoint) {
            const form = document.getElementById(formId);
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Form Keluar validation
                if (formId === 'formKeluar') {
                    let j = parseInt(kJumlah.value) || 0;
                    if (j > currentSaldo) {
                        showToast('Jumlah tidak boleh melebihi saldo tersedia', false);
                        return;
                    }
                }

                if (!form.checkValidity()) {
                    form.classList.add('was-validated');
                    return;
                }

                const formData = new FormData(form);
                const data = Object.fromEntries(formData.entries());

                fetch(endpoint, {
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
                        form.reset();
                        form.classList.remove('was-validated');
                        if(formId === 'formKeluar') {
                            infoSaldo.classList.add('d-none');
                            currentSaldo = 0;
                        }
                        // Reset TomSelect visually
                        document.querySelectorAll(`#${formId} .tomselected`).forEach(el => {
                            if(el.tomselect) el.tomselect.clear();
                        });
                    } else {
                        showToast(res.message, false);
                    }
                })
                .catch(err => {
                    showToast('Terjadi kesalahan server.', false);
                });
            });
        }

        submitForm('formMasuk', '/api/stok/masuk');
        submitForm('formKeluar', '/api/stok/keluar');
    });
</script>
@endsection
