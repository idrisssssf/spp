<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    use HasFactory;
    protected $primaryKey = 'id_transaksi';

    protected $fillable = [
        'nis',
        'total_transaksi',
        'spp',
        'infaq',
        'status_transaksi',
        'id_admin',
        'tanggal_transaksi'
    ];
}
