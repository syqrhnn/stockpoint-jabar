<?php

namespace App\Services;

use Exception;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class StokService
{
    protected NotifikasiService $notifikasiService;

    public function __construct(NotifikasiService $notifikasiService)
    {
        $this->notifikasiService = $notifikasiService;
    }

    /**
     * Memvalidasi bahwa gudang_id sesuai dengan akses user
     * 
     * @param int $gudang_id
     * @param object $user (mengandung properti role dan gudang_id)
     * @throws Exception
     */
    private function validasiAksesGudang(int $gudang_id, $user): void
    {
        if (!in_array($user->role, ['admin_gudang', 'manajer_operasional'])) {
            if ($user->gudang_id != $gudang_id) {
                throw new Exception("Anda tidak memiliki akses untuk mencatat stok di gudang ini.");
            }
        }
    }

    /**
     * Mendapatkan record stok saat ini atau membuatnya jika belum ada
     */
    private function getOrCreateStok(int $barang_id, int $gudang_id): object
    {
        $stok = DB::table('stok')
            ->where('barang_id', $barang_id)
            ->where('gudang_id', $gudang_id)
            ->first();

        if (!$stok) {
            DB::table('stok')->insert([
                'barang_id' => $barang_id,
                'gudang_id' => $gudang_id,
                'saldo' => 0,
                'status' => 'belum_dikonfigurasi',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return DB::table('stok')
                ->where('barang_id', $barang_id)
                ->where('gudang_id', $gudang_id)
                ->first();
        }

        return $stok;
    }

    /**
     * Mencatat transaksi barang masuk
     * 
     * @param array $data ['barang_id', 'gudang_id', 'jumlah', 'tanggal', 'supplier_id', 'catatan']
     * @param object $user 
     * @return object TransaksiStok record
     * @throws Exception
     */
    public function catatStokMasuk(array $data, $user): object
    {
        return DB::transaction(function () use ($data, $user) {
            $tanggal = Carbon::parse($data['tanggal']);
            if ($tanggal->isFuture()) {
                throw new Exception("Tanggal transaksi tidak boleh di masa mendatang.");
            }

            $this->validasiAksesGudang($data['gudang_id'], $user);

            $stok = $this->getOrCreateStok($data['barang_id'], $data['gudang_id']);
            $saldoSebelum = $stok->saldo;
            $saldoSesudah = $saldoSebelum + $data['jumlah'];

            $transaksiId = DB::table('transaksi_stok')->insertGetId([
                'barang_id' => $data['barang_id'],
                'gudang_id' => $data['gudang_id'],
                'user_id' => $user->id,
                'supplier_id' => $data['supplier_id'] ?? null,
                'jenis' => 'masuk',
                'jumlah' => $data['jumlah'],
                'saldo_sebelum' => $saldoSebelum,
                'saldo_sesudah' => $saldoSesudah,
                'tanggal' => $data['tanggal'],
                'catatan' => $data['catatan'] ?? null,
                'created_at' => now(),
            ]);

            DB::table('stok')
                ->where('barang_id', $data['barang_id'])
                ->where('gudang_id', $data['gudang_id'])
                ->update([
                    'saldo' => $saldoSesudah,
                    'status' => $this->evaluasiStatusStok($data['barang_id'], $data['gudang_id'], $saldoSesudah),
                    'updated_at' => now(),
                ]);

            return DB::table('transaksi_stok')->find($transaksiId);
        });
    }

    /**
     * Mencatat transaksi barang keluar
     * 
     * @param array $data ['barang_id', 'gudang_id', 'jumlah', 'tanggal', 'catatan']
     * @param object $user 
     * @return object TransaksiStok record
     * @throws Exception
     */
    public function catatStokKeluar(array $data, $user): object
    {
        return DB::transaction(function () use ($data, $user) {
            $tanggal = Carbon::parse($data['tanggal']);
            if ($tanggal->isFuture()) {
                throw new Exception("Tanggal transaksi tidak boleh di masa mendatang.");
            }

            $this->validasiAksesGudang($data['gudang_id'], $user);

            $stok = $this->getOrCreateStok($data['barang_id'], $data['gudang_id']);
            
            if ($stok->saldo < $data['jumlah']) {
                throw new Exception("Stok tidak mencukupi. Saldo saat ini: {$stok->saldo}");
            }

            $saldoSebelum = $stok->saldo;
            $saldoSesudah = $saldoSebelum - $data['jumlah'];

            $transaksiId = DB::table('transaksi_stok')->insertGetId([
                'barang_id' => $data['barang_id'],
                'gudang_id' => $data['gudang_id'],
                'user_id' => $user->id,
                'supplier_id' => null,
                'jenis' => 'keluar',
                'jumlah' => $data['jumlah'],
                'saldo_sebelum' => $saldoSebelum,
                'saldo_sesudah' => $saldoSesudah,
                'tanggal' => $data['tanggal'],
                'catatan' => $data['catatan'] ?? null,
                'created_at' => now(),
            ]);

            $statusBaru = $this->evaluasiStatusStok($data['barang_id'], $data['gudang_id'], $saldoSesudah);

            DB::table('stok')
                ->where('barang_id', $data['barang_id'])
                ->where('gudang_id', $data['gudang_id'])
                ->update([
                    'saldo' => $saldoSesudah,
                    'status' => $statusBaru,
                    'updated_at' => now(),
                ]);

            if ($statusBaru === 'kritis' && $stok->status !== 'kritis') {
                $this->notifikasiService->kirimPeringatanStokKritis($data['barang_id'], $data['gudang_id'], $saldoSesudah);
            }

            return DB::table('transaksi_stok')->find($transaksiId);
        });
    }

    /**
     * Melakukan koreksi stok
     * 
     * @param array $data ['barang_id', 'gudang_id', 'jumlah_koreksi', 'catatan']
     * @param object $user 
     * @return object TransaksiStok record
     * @throws Exception
     */
    public function stockAdjustment(array $data, $user): object
    {
        return DB::transaction(function () use ($data, $user) {
            if (empty($data['catatan'])) {
                throw new Exception("Alasan koreksi wajib diisi");
            }

            $this->validasiAksesGudang($data['gudang_id'], $user);

            $stok = $this->getOrCreateStok($data['barang_id'], $data['gudang_id']);
            $saldoSebelum = $stok->saldo;
            $saldoSesudah = $saldoSebelum + $data['jumlah_koreksi'];

            if ($saldoSesudah < 0) {
                throw new Exception("Saldo hasil koreksi tidak boleh negatif.");
            }

            $transaksiId = DB::table('transaksi_stok')->insertGetId([
                'barang_id' => $data['barang_id'],
                'gudang_id' => $data['gudang_id'],
                'user_id' => $user->id,
                'supplier_id' => null,
                'jenis' => 'adjustment',
                'jumlah' => $data['jumlah_koreksi'],
                'saldo_sebelum' => $saldoSebelum,
                'saldo_sesudah' => $saldoSesudah,
                'tanggal' => now()->toDateString(),
                'catatan' => $data['catatan'],
                'created_at' => now(),
            ]);

            DB::table('stok')
                ->where('barang_id', $data['barang_id'])
                ->where('gudang_id', $data['gudang_id'])
                ->update([
                    'saldo' => $saldoSesudah,
                    'status' => $this->evaluasiStatusStok($data['barang_id'], $data['gudang_id'], $saldoSesudah),
                    'updated_at' => now(),
                ]);

            DB::table('log_audit')->insert([
                'user_id' => $user->id,
                'tabel_terdampak' => 'transaksi_stok',
                'record_id' => $transaksiId,
                'aksi' => 'stock_adjustment',
                'catatan' => "Koreksi sejumlah {$data['jumlah_koreksi']}. Alasan: {$data['catatan']}",
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return DB::table('transaksi_stok')->find($transaksiId);
        });
    }

    /**
     * Menghitung Average Daily Usage (ADU) berdasarkan 30 hari kalender terakhir
     * 
     * @param int $barang_id
     * @param int $gudang_id
     * @return float
     */
    public function hitungADU(int $barang_id, int $gudang_id): float
    {
        $thirtyDaysAgo = now()->subDays(30)->toDateString();

        $totalKeluar = DB::table('transaksi_stok')
            ->where('barang_id', $barang_id)
            ->where('gudang_id', $gudang_id)
            ->where('jenis', 'keluar')
            ->where('tanggal', '>=', $thirtyDaysAgo)
            ->sum('jumlah');

        if (!$totalKeluar || $totalKeluar == 0) {
            return 0;
        }

        return round($totalKeluar / 30, 2);
    }

    /**
     * Menghitung Reorder Point (ROP) secara statis dari DB
     * 
     * @param int $barang_id
     * @param int $gudang_id
     * @return float|null Null jika parameter belum dikonfigurasi
     */
    public function getROP(int $barang_id, int $gudang_id): ?float
    {
        $parameter = DB::table('rop_parameter')
            ->where('barang_id', $barang_id)
            ->where('gudang_id', $gudang_id)
            ->first();

        if (!$parameter || is_null($parameter->rop)) {
            return null;
        }

        return (float) $parameter->rop;
    }

    /**
     * Mengevaluasi status stok berdasarkan saldo dan ROP
     * 
     * @param int $barang_id
     * @param int $gudang_id
     * @param int|null $saldoTerkini (opsional) Jika tidak diberikan, diambil dari DB
     * @return string 'aman', 'menipis', 'kritis', 'belum_dikonfigurasi'
     */
    public function evaluasiStatusStok(int $barang_id, int $gudang_id, ?int $saldoTerkini = null): string
    {
        $rop = $this->getROP($barang_id, $gudang_id);

        if (is_null($rop)) {
            return 'belum_dikonfigurasi';
        }

        if (is_null($saldoTerkini)) {
            $stok = $this->getOrCreateStok($barang_id, $gudang_id);
            $saldoTerkini = $stok->saldo;
        }

        if ($saldoTerkini <= $rop) {
            return 'kritis';
        }

        if ($saldoTerkini <= ($rop + (0.2 * $rop))) {
            return 'menipis';
        }

        return 'aman';
    }

    /**
     * Simpan Parameter ROP
     * 
     * @param array $data ['barang_id', 'gudang_id', 'lead_time', 'safety_stock']
     * @param object $user
     */
    public function simpanParameterRop(array $data, $user)
    {
        return DB::transaction(function () use ($data, $user) {
            $this->validasiAksesGudang($data['gudang_id'], $user);

            $adu = $this->hitungADU($data['barang_id'], $data['gudang_id']);
            $rop = ($adu * $data['lead_time']) + $data['safety_stock'];

            DB::table('rop_parameter')->updateOrInsert(
                ['barang_id' => $data['barang_id'], 'gudang_id' => $data['gudang_id']],
                [
                    'adu' => $adu,
                    'lead_time' => $data['lead_time'],
                    'safety_stock' => $data['safety_stock'],
                    'rop' => $rop,
                    'updated_by' => $user->id,
                    'updated_at' => now(),
                ]
            );

            // Re-evaluasi stok
            $stok = $this->getOrCreateStok($data['barang_id'], $data['gudang_id']);
            $statusBaru = $this->evaluasiStatusStok($data['barang_id'], $data['gudang_id'], $stok->saldo);
            
            DB::table('stok')
                ->where('barang_id', $data['barang_id'])
                ->where('gudang_id', $data['gudang_id'])
                ->update(['status' => $statusBaru, 'updated_at' => now()]);

            $parameterId = DB::table('rop_parameter')
                ->where('barang_id', $data['barang_id'])
                ->where('gudang_id', $data['gudang_id'])
                ->value('id');

            DB::table('log_audit')->insert([
                'user_id' => $user->id,
                'tabel_terdampak' => 'rop_parameter',
                'record_id' => $parameterId,
                'aksi' => 'Ubah Parameter ROP',
                'catatan' => "Set Lead Time: {$data['lead_time']}, Safety Stock: {$data['safety_stock']} -> ROP: {$rop}",
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return ['adu' => $adu, 'rop' => $rop, 'status' => $statusBaru];
        });
    }
}
