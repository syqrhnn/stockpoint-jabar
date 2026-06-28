<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notifikasi extends Model
{
    use HasFactory;

    protected $table = 'notifikasi';

    // No updated_at in the schema
    const UPDATED_AT = null;

    protected $fillable = [
        'barang_id',
        'gudang_id',
        'pesan',
        'status',
    ];
}
