@extends('layouts.app')
@section('title', 'Koreksi Stok (Adjustment)')

@section('content')
<div class="container-fluid p-4">
    <h2 class="mb-4 fw-bold">Koreksi Stok (Adjustment)</h2>

    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card shadow-sm border-0 border-top border-warning border-4">
                <div class="card-header bg-white pt-3 pb-2">
                    <h5 class="card-title fw-bold text-warning mb-0"><i class="bi bi-exclamation-triangle-fill"></i> Peringatan Audit</h5>
                    <small class="text-muted">Setiap koreksi stok yang Anda lakukan di halaman ini akan tercatat permanen di dalam log audit sistem.</small>
                </div>
                <div class="card-body p-4">
                    <form id="formAdjustment" class="needs-validation" novalidate>
                        @csrf
                        
                        @if(session('role') === 'admin_gudang')
                        <div class="mb-3">
                            <label class="form-label fw-bold">Gudang <span class="text-danger">*</span></label>
                            <select class="form-select" name="gudang_id" id="adj_gudang_id" required>
                                <option value="">Pilih Gudang...</option>
                                @foreach($gudang as $g)
                                    <option value="{{ $g->id }}">{{ $g->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        @else
                            <input type="hidden" name="gudang_id" id="adj_gudang_id" value="{{ session('gudang_id') }}">
                        @endif

                        <div class="mb-3">
                            <label class="form-label fw-bold">Barang <span class="text-danger">*</span></label>
                            <select class="form-select select-barang" name="barang_id" id="adj_barang_id" required>
                                <option value="">Pilih Barang...</option>
                                @foreach($barang as $b)
                                    <option value="{{ $b->id }}">{{ $b->nama }} ({{ $b->satuan }})</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Live Preview Panel -->
                        <div class="card bg-light border-0 mb-4 d-none" id="previewPanel">
                            <div class="card-body d-flex justify-content-between align-items-center text-center">
                                <div>
                                    <div class="small text-muted text-uppercase fw-bold">Saldo Awal</div>
                                    <div class="fs-3 fw-bold" id="lblSaldoAwal">0</div>
                                </div>
                                <div class="fs-4 text-muted"><i class="bi bi-arrow-right"></i></div>
                                <div>
                                    <div class="small text-muted text-uppercase fw-bold">Koreksi</div>
                                    <div class="fs-3 fw-bold text-primary" id="lblKoreksi">0</div>
                                </div>
                                <div class="fs-4 text-muted"><i class="bi bi-pause" style="transform: rotate(90deg); display: inline-block;"></i></div>
                                <div>
                                    <div class="small text-muted text-uppercase fw-bold">Saldo Baru</div>
                                    <div class="fs-3 fw-bold" id="lblSaldoBaru">0</div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Jumlah Koreksi <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-plus-slash-minus"></i></span>
                                <input type="number" class="form-control" name="jumlah_koreksi" id="adj_jumlah" required placeholder="Gunakan tanda minus (-) untuk pengurangan">
                            </div>
                            <div class="form-text">Contoh: <b>5</b> untuk menambah 5 unit, <b>-3</b> untuk mengurangi 3 unit.</div>
                            <div class="invalid-feedback" id="invalidJumlah">Nilai koreksi tidak valid (saldo akhir menjadi negatif).</div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Alasan Koreksi <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="catatan" id="adj_catatan" rows="3" required placeholder="Jelaskan alasan spesifik dilakukannya koreksi stok ini..."></textarea>
                            <div class="invalid-feedback">Alasan koreksi wajib diisi.</div>
                        </div>

                        <button type="submit" class="btn btn-warning w-100 fw-bold py-2" id="btnSubmit"><i class="bi bi-check2-circle"></i> Simpan Koreksi Permanen</button>
                    </form>
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
        document.querySelectorAll('.select-barang').forEach(el => {
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

        const aGudang = document.getElementById('adj_gudang_id');
        const aBarang = document.getElementById('adj_barang_id');
        const aJumlah = document.getElementById('adj_jumlah');
        const aCatatan = document.getElementById('adj_catatan');
        const btnSubmit = document.getElementById('btnSubmit');
        
        const previewPanel = document.getElementById('previewPanel');
        const lblSaldoAwal = document.getElementById('lblSaldoAwal');
        const lblKoreksi = document.getElementById('lblKoreksi');
        const lblSaldoBaru = document.getElementById('lblSaldoBaru');

        let currentSaldo = 0;

        function fetchSaldo() {
            let gid = aGudang.value;
            let bid = aBarang.value;

            if (gid && bid) {
                fetch(`/api/stok/saldo?gudang_id=${gid}&barang_id=${bid}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            currentSaldo = data.saldo;
                            lblSaldoAwal.textContent = currentSaldo;
                            previewPanel.classList.remove('d-none');
                            calculatePreview();
                        }
                    });
            } else {
                previewPanel.classList.add('d-none');
                currentSaldo = 0;
            }
        }

        function calculatePreview() {
            let val = parseInt(aJumlah.value) || 0;
            lblKoreksi.textContent = (val > 0 ? '+' : '') + val;
            let newSaldo = currentSaldo + val;
            lblSaldoBaru.textContent = newSaldo;

            if (newSaldo < 0) {
                lblSaldoBaru.classList.add('text-danger');
                lblSaldoBaru.classList.remove('text-success');
                aJumlah.setCustomValidity('Invalid'); // Trigger native invalid
                document.getElementById('invalidJumlah').style.display = 'block';
                btnSubmit.disabled = true;
            } else {
                lblSaldoBaru.classList.remove('text-danger');
                lblSaldoBaru.classList.add('text-success');
                aJumlah.setCustomValidity('');
                document.getElementById('invalidJumlah').style.display = 'none';
                btnSubmit.disabled = false;
            }
        }

        if(aGudang.tagName === 'SELECT') { aGudang.addEventListener('change', fetchSaldo); }
        aBarang.addEventListener('change', fetchSaldo);
        aJumlah.addEventListener('input', calculatePreview);

        // Submit form
        const form = document.getElementById('formAdjustment');
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (aCatatan.value.trim() === '') {
                aCatatan.setCustomValidity('Invalid');
            } else {
                aCatatan.setCustomValidity('');
            }

            if (!form.checkValidity()) {
                form.classList.add('was-validated');
                return;
            }

            btnSubmit.disabled = true;
            const data = {
                gudang_id: aGudang.value,
                barang_id: aBarang.value,
                jumlah_koreksi: aJumlah.value,
                catatan: aCatatan.value
            };

            fetch('/api/stok/adjustment', {
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
                    previewPanel.classList.add('d-none');
                    document.querySelectorAll('.tomselected').forEach(el => {
                        if(el.tomselect) el.tomselect.clear();
                    });
                } else {
                    showToast(res.message, false);
                }
            })
            .catch(err => {
                showToast('Terjadi kesalahan server.', false);
            })
            .finally(() => {
                btnSubmit.disabled = false;
            });
        });
    });
</script>
@endsection
