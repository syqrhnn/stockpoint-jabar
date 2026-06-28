@extends('layouts.app')

@section('title', 'Data Barang')

@section('content')
<div class="card shadow-sm border-0">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
        <h5 class="mb-0 text-primary"><i class="bi bi-box"></i> Manajemen Data Barang</h5>
        <button class="btn btn-primary btn-sm" onclick="showModal()">
            <i class="bi bi-plus-lg"></i> Tambah Barang
        </button>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-4">
                <input type="text" id="searchInput" class="form-control" placeholder="Cari nama barang...">
            </div>
            <div class="col-md-3">
                <select id="kategoriFilter" class="form-select">
                    <option value="">Semua Kategori</option>
                    <option value="Bahan Baku">Bahan Baku</option>
                    <option value="Barang Jadi">Barang Jadi</option>
                    <option value="Kemasan">Kemasan</option>
                    <option value="Alat Tulis">Alat Tulis</option>
                    <option value="Lainnya">Lainnya</option>
                </select>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Nama Barang</th>
                        <th>Kategori</th>
                        <th>Satuan</th>
                        <th class="text-center" width="150">Aksi</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <tr><td colspan="5" class="text-center">Memuat data...</td></tr>
                </tbody>
            </table>
        </div>
        
        <div class="d-flex justify-content-between align-items-center mt-3" id="paginationContainer">
            <small class="text-muted" id="pageInfo"></small>
            <div class="btn-group">
                <button id="prevBtn" class="btn btn-outline-secondary btn-sm" onclick="changePage(currentPage - 1)">Sebelumnya</button>
                <button id="nextBtn" class="btn btn-outline-secondary btn-sm" onclick="changePage(currentPage + 1)">Selanjutnya</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Form -->
<div class="modal fade" id="formModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="dataForm" onsubmit="saveData(event)">
          <div class="modal-header">
            <h5 class="modal-title" id="modalTitle">Tambah Barang</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
              <input type="hidden" id="dataId">
              <div class="mb-3">
                  <label class="form-label">Nama Barang <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" id="nama" required maxlength="100">
                  <div class="invalid-feedback" id="err_nama"></div>
              </div>
              <div class="mb-3">
                  <label class="form-label">Kategori <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" id="kategori" required maxlength="50" placeholder="Contoh: Bahan Baku">
                  <div class="invalid-feedback" id="err_kategori"></div>
              </div>
              <div class="mb-3">
                  <label class="form-label">Satuan <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" id="satuan" required maxlength="20" placeholder="Contoh: kg, pcs, box">
                  <div class="invalid-feedback" id="err_satuan"></div>
              </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-primary" id="btnSave">Simpan</button>
          </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Delete -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <div class="modal-body text-center p-4">
        <i class="bi bi-exclamation-circle text-danger" style="font-size: 3rem;"></i>
        <h5 class="mt-3">Hapus Data?</h5>
        <p class="text-muted">Data yang dihapus tidak dapat dikembalikan.</p>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-danger" onclick="confirmDelete()">Hapus</button>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
    const API_URL = '/admin/api/barang';
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    
    let currentPage = 1;
    let currentSearch = '';
    let currentKategori = '';
    let deleteId = null;
    let formModal = null;
    let deleteModal = null;
    let searchTimeout = null;

    document.addEventListener('DOMContentLoaded', function() {
        formModal = new bootstrap.Modal(document.getElementById('formModal'));
        deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
        
        loadData();

        document.getElementById('searchInput').addEventListener('input', function(e) {
            clearTimeout(searchTimeout);
            currentSearch = e.target.value;
            searchTimeout = setTimeout(() => {
                currentPage = 1;
                loadData();
            }, 300);
        });

        document.getElementById('kategoriFilter').addEventListener('change', function(e) {
            currentKategori = e.target.value;
            currentPage = 1;
            loadData();
        });
    });

    function loadData() {
        const query = new URLSearchParams({
            page: currentPage,
            search: currentSearch,
            kategori: currentKategori
        });

        fetch(`${API_URL}?${query.toString()}`, {
            headers: { 'Accept': 'application/json' }
        })
        .then(res => res.json())
        .then(res => {
            if(res.success) renderTable(res.data);
        })
        .catch(err => showToast('Gagal mengambil data', 'error'));
    }

    function renderTable(paginator) {
        const tbody = document.getElementById('tableBody');
        tbody.innerHTML = '';
        
        if (paginator.data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">Tidak ada data ditemukan.</td></tr>';
        } else {
            paginator.data.forEach(item => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${item.id}</td>
                    <td class="fw-bold">${item.nama}</td>
                    <td><span class="badge bg-secondary">${item.kategori}</span></td>
                    <td>${item.satuan}</td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-outline-primary" onclick='editData(${JSON.stringify(item)})'><i class="bi bi-pencil"></i></button>
                        <button class="btn btn-sm btn-outline-danger" onclick="promptDelete(${item.id})"><i class="bi bi-trash"></i></button>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        }

        document.getElementById('pageInfo').innerText = `Menampilkan ${paginator.from || 0} - ${paginator.to || 0} dari ${paginator.total} data`;
        document.getElementById('prevBtn').disabled = !paginator.prev_page_url;
        document.getElementById('nextBtn').disabled = !paginator.next_page_url;
    }

    function changePage(page) {
        currentPage = page;
        loadData();
    }

    function showModal() {
        document.getElementById('dataForm').reset();
        document.getElementById('dataId').value = '';
        document.getElementById('modalTitle').innerText = 'Tambah Barang';
        clearErrors();
        formModal.show();
    }

    function editData(item) {
        clearErrors();
        document.getElementById('modalTitle').innerText = 'Edit Barang';
        document.getElementById('dataId').value = item.id;
        document.getElementById('nama').value = item.nama;
        document.getElementById('kategori').value = item.kategori;
        document.getElementById('satuan').value = item.satuan;
        formModal.show();
    }

    function clearErrors() {
        document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    }

    function saveData(e) {
        e.preventDefault();
        clearErrors();
        const id = document.getElementById('dataId').value;
        const payload = {
            nama: document.getElementById('nama').value,
            kategori: document.getElementById('kategori').value,
            satuan: document.getElementById('satuan').value,
        };

        const url = id ? `${API_URL}/${id}` : API_URL;
        const method = id ? 'PUT' : 'POST';
        const btn = document.getElementById('btnSave');
        btn.disabled = true;

        fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify(payload)
        })
        .then(async res => {
            const data = await res.json();
            if (!res.ok) {
                if (res.status === 422) {
                    for (const key in data.errors) {
                        const input = document.getElementById(key);
                        const errDiv = document.getElementById(`err_${key}`);
                        if(input && errDiv) {
                            input.classList.add('is-invalid');
                            errDiv.innerText = data.errors[key][0];
                        }
                    }
                } else {
                    showToast(data.message || 'Terjadi kesalahan sistem', 'error');
                }
                return;
            }
            formModal.hide();
            showToast(data.message, 'success');
            loadData();
        })
        .catch(err => showToast('Gagal menyimpan data', 'error'))
        .finally(() => btn.disabled = false);
    }

    function promptDelete(id) {
        deleteId = id;
        deleteModal.show();
    }

    function confirmDelete() {
        fetch(`${API_URL}/${deleteId}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            }
        })
        .then(res => res.json().then(data => ({status: res.status, ok: res.ok, body: data})))
        .then(res => {
            deleteModal.hide();
            if (res.ok) {
                showToast(res.body.message, 'success');
                loadData();
            } else {
                showToast(res.body.message || 'Gagal menghapus data', 'error');
            }
        })
        .catch(err => {
            deleteModal.hide();
            showToast('Terjadi kesalahan jaringan', 'error');
        });
    }
</script>
@endpush
