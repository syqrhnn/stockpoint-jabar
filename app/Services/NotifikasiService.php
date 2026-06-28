<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Notifikasi;

class NotifikasiService
{
    /**
     * Membuat notifikasi peringatan stok kritis jika belum ada yang 'belum_dibaca'
     *
     * @param int $barang_id
     * @param int $gudang_id
     * @param int $saldo_aktual
     * @param float $rop
     * @return void
     */
    public function buatNotifikasiKritis(int $barang_id, int $gudang_id, int $saldo_aktual, float $rop): void
    {
        // Cek apakah sudah ada notifikasi belum dibaca untuk barang & gudang ini
        $exists = Notifikasi::where('barang_id', $barang_id)
            ->where('gudang_id', $gudang_id)
            ->where('status', 'belum_dibaca')
            ->exists();

        if ($exists) {
            return; // Hindari duplikasi
        }

        $barang = DB::table('barang')->find($barang_id);
        $gudang = DB::table('gudang')->find($gudang_id);
        
        $namaBarang = $barang ? $barang->nama : "ID {$barang_id}";
        $namaGudang = $gudang ? $gudang->nama : "ID {$gudang_id}";

        $pesan = "{$namaBarang} di {$namaGudang} mencapai status KRITIS. Stok tersisa: {$saldo_aktual} unit. ROP: {$rop} unit.";
        
        Notifikasi::create([
            'barang_id' => $barang_id,
            'gudang_id' => $gudang_id,
            'pesan' => $pesan,
            'status' => 'belum_dibaca',
        ]);
    }
}
