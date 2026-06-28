@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="card shadow-sm border-0">
    <div class="card-body">
        <h5 class="card-title text-primary">Selamat Datang, {{ session('nama') }}!</h5>
        <p class="card-text">Anda berhasil login sebagai <strong>{{ str_replace('_', ' ', strtoupper(session('role'))) }}</strong>.</p>
        
        @if(session('gudang_id'))
            <p class="text-muted mb-0"><i class="bi bi-building"></i> Gudang ID Anda: {{ session('gudang_id') }}</p>
        @else
            <p class="text-muted mb-0"><i class="bi bi-building"></i> Akses Data: Seluruh Gudang</p>
        @endif
    </div>
</div>
@endsection
