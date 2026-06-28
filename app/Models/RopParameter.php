<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RopParameter extends Model
{
    use HasFactory;

    protected $table = 'rop_parameter';

    protected $fillable = [
        'barang_id',
        'gudang_id',
        'adu',
        'lead_time',
        'safety_stock',
        'rop',
        'updated_by',
    ];

    public function barang()
    {
        return $this->belongsTo(Barang::class);
    }

    public function gudang()
    {
        return $this->belongsTo(Gudang::class);
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
