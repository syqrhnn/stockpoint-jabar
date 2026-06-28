@extends('layouts.app')
@section('title', 'System Check & Go-Live Finalization')

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">System Check & Go-Live</h2>
            <p class="text-muted mb-0">Verifikasi kelengkapan data dan konfigurasi sistem sebelum perilisan penuh.</p>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4">Checklist Kesiapan Sistem</h5>
                    
                    <ul class="list-group list-group-flush">
                        @foreach($checklist as $item)
                        <li class="list-group-item d-flex justify-content-between align-items-start py-3 px-0 border-bottom">
                            <div class="ms-2 me-auto">
                                <div class="fw-bold {{ $item['ok'] ? 'text-dark' : 'text-danger' }}">
                                    {{ $item['label'] }}
                                </div>
                                <div class="small text-muted mt-1">{{ $item['detail'] }}</div>
                            </div>
                            <span class="badge rounded-pill p-2 {{ $item['ok'] ? 'bg-success bg-opacity-10 text-success' : 'bg-danger bg-opacity-10 text-danger' }}">
                                @if($item['ok'])
                                    <i class="bi bi-check-circle-fill fs-5"></i>
                                @else
                                    <i class="bi bi-x-circle-fill fs-5"></i>
                                @endif
                            </span>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 text-center {{ $allOk ? 'bg-success text-white' : 'bg-warning text-dark' }}">
                <div class="card-body p-5">
                    <i class="bi {{ $allOk ? 'bi-rocket-takeoff-fill' : 'bi-exclamation-triangle-fill' }} display-1 mb-3"></i>
                    <h3 class="fw-bold">{{ $allOk ? 'Sistem Siap!' : 'Sistem Belum Siap' }}</h3>
                    <p class="{{ $allOk ? 'text-white-50' : 'text-muted' }}">
                        @if($allOk)
                            Semua checklist telah terpenuhi. StockPoint Jabar siap untuk Go-Live.
                        @else
                            Harap selesaikan semua item checklist yang ditandai merah sebelum melakukan Go-Live.
                        @endif
                    </p>
                    
                    @if(!$allOk)
                    <div class="mt-4">
                        <p class="small fw-bold mb-2">Tindakan yang Diperlukan:</p>
                        <div class="d-grid gap-2">
                             <a href="{{ route('admin.pengguna.index') }}" class="btn btn-outline-dark btn-sm">Kelola Pengguna</a>
                             <a href="{{ route('rop.index') }}" class="btn btn-outline-dark btn-sm">Konfigurasi ROP</a>
                             <a href="/stok/opening-balance" class="btn btn-outline-dark btn-sm">Input Opening Balance</a>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
