<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Notifikasi;

class NotifikasiService
{
    /**
     * Mengirimkan notifikasi peringatan stok kritis
     *
     * @param int $barang_id
     * @param int $gudang_id
     * @param float|int $saldo
     * @return void
     */
    public function kirimPeringatanStokKritis(int $barang_id, int $gudang_id, $saldo): void
    {
        $barang = DB::table('barang')->find($barang_id);
        $gudang = DB::table('gudang')->find($gudang_id);
        
        $namaBarang = $barang ? $barang->nama : "ID {$barang_id}";
        $namaGudang = $gudang ? $gudang->nama : "ID {$gudang_id}";

        $judul = "Stok Kritis: {$namaBarang}";
        $pesan = "Stok barang {$namaBarang} di gudang {$namaGudang} telah mencapai level KRITIS. Saldo saat ini: {$saldo} unit. Segera lakukan penambahan stok (replenishment).";
        
        Log::warning($pesan);
        
        // Simpan ke tabel notifikasi (user_id null = notifikasi global untuk semua admin/manajer)
        Notifikasi::create([
            'user_id' => null,
            'judul' => $judul,
            'pesan' => $pesan,
            'is_read' => false,
            'link' => '/stok/catat',
        ]);
    }
}
