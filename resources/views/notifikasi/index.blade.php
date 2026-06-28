@extends('layouts.app')
@section('title', 'Riwayat Notifikasi')

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0">Riwayat Notifikasi</h2>
        <div class="d-flex gap-2">
            <select class="form-select w-auto" id="filterStatus">
                <option value="">Semua Status</option>
                <option value="belum_dibaca">Belum Dibaca</option>
                <option value="sudah_dibaca">Sudah Dibaca</option>
            </select>
            <button class="btn btn-primary" id="btnMarkAllRead">
                <i class="bi bi-check2-all"></i> Tandai Semua Dibaca
            </button>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-muted small">
                        <tr>
                            <th class="ps-4">Waktu</th>
                            <th>Pesan Notifikasi</th>
                            <th>Status</th>
                            <th class="pe-4 text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tableNotifikasi">
                        <tr><td colspan="4" class="text-center py-4">Memuat...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-0 py-3" id="paginationContainer">
            <!-- Pagination buttons -->
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const tableBody = document.getElementById('tableNotifikasi');
    const paginationContainer = document.getElementById('paginationContainer');
    const filterStatus = document.getElementById('filterStatus');
    const btnMarkAllRead = document.getElementById('btnMarkAllRead');

    let currentPage = 1;

    function fetchNotifikasi(page = 1) {
        let url = `/api/notifikasi/riwayat?page=${page}`;
        if (filterStatus.value) {
            url += `&status=${filterStatus.value}`;
        }

        fetch(url, {
            headers: { 'Accept': 'application/json' }
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                renderTable(data.data.data);
                renderPagination(data.data);
                currentPage = data.data.current_page;
            }
        })
        .catch(err => console.error(err));
    }

    function renderTable(rows) {
        if(rows.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="4" class="text-center py-4 text-muted">Tidak ada notifikasi.</td></tr>';
            return;
        }

        let html = '';
        rows.forEach(r => {
            let bgClass = r.status === 'belum_dibaca' ? 'bg-light' : '';
            let fwClass = r.status === 'belum_dibaca' ? 'fw-bold' : '';
            let badge = r.status === 'belum_dibaca' 
                ? '<span class="badge bg-danger rounded-pill">Belum Dibaca</span>' 
                : '<span class="badge bg-secondary rounded-pill">Sudah Dibaca</span>';
            
            let actionBtn = r.status === 'belum_dibaca'
                ? `<button class="btn btn-sm btn-outline-primary" onclick="markRowRead(${r.id})"><i class="bi bi-check2"></i> Tandai Dibaca</button>`
                : '';

            html += `
                <tr class="${bgClass}">
                    <td class="ps-4">
                        <div class="small text-muted mb-1">${new Date(r.created_at).toLocaleString()}</div>
                        <div class="small fw-bold text-primary">${r.waktu_relatif}</div>
                    </td>
                    <td class="${fwClass}">${r.pesan}</td>
                    <td>${badge}</td>
                    <td class="pe-4 text-end">${actionBtn}</td>
                </tr>
            `;
        });
        tableBody.innerHTML = html;
    }

    function renderPagination(meta) {
        if (meta.last_page <= 1) {
            paginationContainer.innerHTML = '';
            return;
        }

        let html = '<div class="d-flex justify-content-between align-items-center"><div class="small text-muted">Menampilkan ' + meta.from + ' sampai ' + meta.to + ' dari ' + meta.total + ' data</div><ul class="pagination pagination-sm mb-0">';
        
        meta.links.forEach(link => {
            let label = link.label.replace('&laquo;', '«').replace('&raquo;', '»');
            let disabled = link.url ? '' : 'disabled';
            let active = link.active ? 'active' : '';
            let pageNum = link.url ? new URL(link.url).searchParams.get('page') : '#';

            html += `<li class="page-item ${disabled} ${active}"><a class="page-link" href="#" data-page="${pageNum}">${label}</a></li>`;
        });

        html += '</ul></div>';
        paginationContainer.innerHTML = html;

        // Bind clicks
        paginationContainer.querySelectorAll('.page-link').forEach(a => {
            a.addEventListener('click', function(e) {
                e.preventDefault();
                let page = this.getAttribute('data-page');
                if (page && page !== '#') fetchNotifikasi(page);
            });
        });
    }

    window.markRowRead = function(id) {
        // Also call global function from app.blade.php so header updates
        if(typeof window.markAsRead === 'function') {
            window.markAsRead(id);
        } else {
            // Fallback if not loaded
            fetch(`/api/notifikasi/${id}/read`, {
                method: 'PATCH',
                headers: { 
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content 
                }
            })
            .then(res => res.json())
            .then(data => { if(data.success) fetchNotifikasi(currentPage); });
        }
    }

    // Listener for custom event from app.blade.php
    window.addEventListener('notifikasiUpdated', function() {
        fetchNotifikasi(currentPage);
    });

    btnMarkAllRead.addEventListener('click', function() {
        fetch('/api/notifikasi/mark-all-read', {
            method: 'PATCH',
            headers: { 
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content 
            }
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                fetchNotifikasi(currentPage);
                // Trigger global update
                if(typeof window.markAsRead === 'function') window.dispatchEvent(new Event('notifikasiUpdated')); // Just a hack to force re-fetch in layout too
                // Oh wait, layout poll uses interval. We can just wait for next poll or trigger it manually.
                // It's handled by markAllReadBtn in app.blade.php already. But we clicked the one in index page.
                // Let's trigger click on the header button to sync
                const headerBtn = document.getElementById('markAllReadBtn');
                if(headerBtn) headerBtn.click();
            }
        });
    });

    filterStatus.addEventListener('change', () => fetchNotifikasi(1));

    fetchNotifikasi();
});
</script>
@endpush
