<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransaksiStok extends Model
{
    use HasFactory;

    protected $table = 'transaksi_stok';

    // Transaksi stok is immutable, so no updated_at column
    public const UPDATED_AT = null;

    protected $fillable = [
        'barang_id',
        'gudang_id',
        'jenis',
        'jumlah',
        'saldo_sebelum',
        'saldo_sesudah',
        'tanggal',
        'supplier_id',
        'user_id',
        'catatan',
    ];

    /**
     * Enforce immutability.
     */
    protected static function booted()
    {
        static::updating(function ($model) {
            throw new \Exception("Transaksi Stok bersifat immutable dan tidak dapat diubah.");
        });

        static::deleting(function ($model) {
            throw new \Exception("Transaksi Stok bersifat immutable dan tidak dapat dihapus.");
        });
    }

    public function barang()
    {
        return $this->belongsTo(Barang::class);
    }

    public function gudang()
    {
        return $this->belongsTo(Gudang::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
