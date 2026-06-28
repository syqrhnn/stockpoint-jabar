<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class NotifikasiService
{
    /**
     * Mengirimkan notifikasi peringatan stok kritis
     * Saat ini diimplementasikan sebagai logger (stub)
     *
     * @param int $barang_id
     * @param int $gudang_id
     * @param float|int $saldo
     * @return void
     */
    public function kirimPeringatanStokKritis(int $barang_id, int $gudang_id, $saldo): void
    {
        $pesan = "PERINGATAN STOK KRITIS! Barang ID: {$barang_id} di Gudang ID: {$gudang_id} telah mencapai level kritis. Saldo saat ini: {$saldo}.";
        
        Log::warning($pesan);
        
        // TODO: Implementasi notifikasi sistem sesungguhnya (Email/Web Push) dapat dilakukan di sini kelak.
    }
}
