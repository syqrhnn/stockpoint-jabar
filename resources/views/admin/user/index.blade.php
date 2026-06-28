@extends('layouts.app')

@section('title', 'Manajemen Pengguna')

@section('content')
<div class="card shadow-sm border-0">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
        <h5 class="mb-0 text-primary"><i class="bi bi-people"></i> Manajemen Pengguna</h5>
        <button class="btn btn-primary btn-sm" onclick="showModal()">
            <i class="bi bi-person-plus"></i> Tambah Pengguna
        </button>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-4 mb-2">
                <input type="text" id="searchInput" class="form-control" placeholder="Cari nama atau email...">
            </div>
            <div class="col-md-3 mb-2">
                <select id="roleFilter" class="form-select">
                    <option value="">Semua Role</option>
                    <option value="admin_gudang">Admin Gudang</option>
                    <option value="kepala_gudang">Kepala Gudang</option>
                    <option value="staf_gudang">Staf Gudang</option>
                    <option value="manajer_operasional">Manajer Operasional</option>
                </select>
            </div>
            <div class="col-md-3 mb-2">
                <select id="statusFilter" class="form-select">
                    <option value="">Semua Status</option>
                    <option value="1">Aktif</option>
                    <option value="0">Nonaktif</option>
                </select>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Nama Lengkap</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Gudang</th>
                        <th>Status</th>
                        <th class="text-center" width="150">Aksi</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <tr><td colspan="7" class="text-center">Memuat data...</td></tr>
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
            <h5 class="modal-title" id="modalTitle">Tambah Pengguna</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
              <input type="hidden" id="dataId">
              <div class="mb-3">
                  <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" id="nama" required maxlength="100">
                  <div class="invalid-feedback" id="err_nama"></div>
              </div>
              <div class="mb-3">
                  <label class="form-label">Email <span class="text-danger">*</span></label>
                  <input type="email" class="form-control" id="email" required maxlength="255">
                  <div class="invalid-feedback" id="err_email"></div>
              </div>
              <div class="mb-3" id="passwordGroup">
                  <label class="form-label">Password <span class="text-danger">*</span></label>
                  <input type="password" class="form-control" id="password" minlength="8">
                  <small class="text-muted" id="passwordHint">Minimal 8 karakter.</small>
                  <div class="invalid-feedback" id="err_password"></div>
              </div>
              <div class="mb-3">
                  <label class="form-label">Role <span class="text-danger">*</span></label>
                  <select class="form-select" id="role" required onchange="handleRoleChange()">
                      <option value="">Pilih Role...</option>
                      <option value="admin_gudang">Admin Gudang</option>
                      <option value="kepala_gudang">Kepala Gudang</option>
                      <option value="staf_gudang">Staf Gudang</option>
                      <option value="manajer_operasional">Manajer Operasional</option>
                  </select>
                  <div class="invalid-feedback" id="err_role"></div>
              </div>
              <div class="mb-3" id="gudangGroup" style="display: none;">
                  <label class="form-label">Penempatan Gudang <span class="text-danger">*</span></label>
                  <select class="form-select" id="gudang_id">
                      <option value="">Pilih Gudang...</option>
                  </select>
                  <div class="invalid-feedback" id="err_gudang_id"></div>
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

<!-- Modal Deactivate -->
<div class="modal fade" id="deactivateModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <div class="modal-body text-center p-4">
        <i class="bi bi-person-x text-warning" style="font-size: 3rem;"></i>
        <h5 class="mt-3">Nonaktifkan Akun?</h5>
        <p class="text-muted" id="deactivateText"></p>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-warning" onclick="confirmDeactivate()">Nonaktifkan</button>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
    const API_URL = '/admin/api/pengguna';
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    const currentUserId = {{ session('user_id') }};
    
    let currentPage = 1;
    let currentSearch = '';
    let currentRole = '';
    let currentStatus = '';
    let actionId = null;
    let actionName = '';
    let formModal = null;
    let deactivateModal = null;
    let searchTimeout = null;

    document.addEventListener('DOMContentLoaded', function() {
        formModal = new bootstrap.Modal(document.getElementById('formModal'));
        deactivateModal = new bootstrap.Modal(document.getElementById('deactivateModal'));
        
        loadGudangList();
        loadData();

        document.getElementById('searchInput').addEventListener('input', function(e) {
            clearTimeout(searchTimeout);
            currentSearch = e.target.value;
            searchTimeout = setTimeout(() => {
                currentPage = 1;
                loadData();
            }, 300);
        });

        document.getElementById('roleFilter').addEventListener('change', function(e) {
            currentRole = e.target.value;
            currentPage = 1;
            loadData();
        });

        document.getElementById('statusFilter').addEventListener('change', function(e) {
            currentStatus = e.target.value;
            currentPage = 1;
            loadData();
        });
    });

    function loadGudangList() {
        fetch(`${API_URL}/gudang-list`)
            .then(res => res.json())
            .then(res => {
                if(res.success) {
                    const select = document.getElementById('gudang_id');
                    res.data.forEach(g => {
                        const option = document.createElement('option');
                        option.value = g.id;
                        option.textContent = g.nama;
                        select.appendChild(option);
                    });
                }
            });
    }

    function handleRoleChange() {
        const role = document.getElementById('role').value;
        const gudangGroup = document.getElementById('gudangGroup');
        const gudangInput = document.getElementById('gudang_id');
        
        if (role === 'kepala_gudang' || role === 'staf_gudang') {
            gudangGroup.style.display = 'block';
            gudangInput.setAttribute('required', 'required');
        } else {
            gudangGroup.style.display = 'none';
            gudangInput.removeAttribute('required');
            gudangInput.value = '';
        }
    }

    function loadData() {
        const query = new URLSearchParams({
            page: currentPage,
            search: currentSearch,
            role: currentRole,
            is_active: currentStatus
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

    function formatRole(role) {
        if(role === 'admin_gudang') return '<span class="badge bg-danger">Admin Gudang</span>';
        if(role === 'kepala_gudang') return '<span class="badge bg-primary">Kepala Gudang</span>';
        if(role === 'staf_gudang') return '<span class="badge bg-success">Staf Gudang</span>';
        if(role === 'manajer_operasional') return '<span class="badge bg-secondary">Manajer Ops</span>';
        return role;
    }

    function renderTable(paginator) {
        const tbody = document.getElementById('tableBody');
        tbody.innerHTML = '';
        
        if (paginator.data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted">Tidak ada data ditemukan.</td></tr>';
        } else {
            paginator.data.forEach(item => {
                const tr = document.createElement('tr');
                const statusBadge = item.is_active == 1 
                    ? '<span class="badge bg-success">Aktif</span>' 
                    : '<span class="badge bg-danger">Nonaktif</span>';
                
                const isMe = item.id === currentUserId;
                const disableDeactivate = (isMe || item.is_active == 0) ? 'disabled' : '';

                tr.innerHTML = `
                    <td>${item.id}</td>
                    <td class="fw-bold">${item.nama} ${isMe ? '<span class="badge bg-info text-dark ms-1">Anda</span>' : ''}</td>
                    <td>${item.email}</td>
                    <td>${formatRole(item.role)}</td>
                    <td>${item.gudang_nama || '-'}</td>
                    <td>${statusBadge}</td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-outline-primary" onclick='editData(${JSON.stringify(item)})'><i class="bi bi-pencil"></i></button>
                        <button class="btn btn-sm btn-outline-warning" onclick="promptDeactivate(${item.id}, '${item.nama}')" ${disableDeactivate}><i class="bi bi-person-x"></i></button>
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
        document.getElementById('modalTitle').innerText = 'Tambah Pengguna';
        document.getElementById('passwordGroup').style.display = 'block';
        document.getElementById('password').setAttribute('required', 'required');
        clearErrors();
        handleRoleChange();
        formModal.show();
    }

    function editData(item) {
        clearErrors();
        document.getElementById('dataForm').reset();
        document.getElementById('modalTitle').innerText = 'Edit Pengguna';
        document.getElementById('dataId').value = item.id;
        document.getElementById('nama').value = item.nama;
        document.getElementById('email').value = item.email;
        document.getElementById('role').value = item.role;
        document.getElementById('gudang_id').value = item.gudang_id || '';
        
        document.getElementById('passwordGroup').style.display = 'none';
        document.getElementById('password').removeAttribute('required');
        
        handleRoleChange();
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
            email: document.getElementById('email').value,
            role: document.getElementById('role').value,
            gudang_id: document.getElementById('gudang_id').value
        };

        if (!id) {
            payload.password = document.getElementById('password').value;
        }

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

    function promptDeactivate(id, nama) {
        actionId = id;
        document.getElementById('deactivateText').innerText = `Akun ${nama} akan dinonaktifkan dan tidak dapat login. Lanjutkan?`;
        deactivateModal.show();
    }

    function confirmDeactivate() {
        fetch(`${API_URL}/${actionId}/deactivate`, {
            method: 'PATCH',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            }
        })
        .then(res => res.json().then(data => ({status: res.status, ok: res.ok, body: data})))
        .then(res => {
            deactivateModal.hide();
            if (res.ok) {
                showToast(res.body.message, 'success');
                loadData();
            } else {
                showToast(res.body.message || 'Gagal menonaktifkan akun', 'error');
            }
        })
        .catch(err => {
            deactivateModal.hide();
            showToast('Terjadi kesalahan jaringan', 'error');
        });
    }
</script>
@endpush
