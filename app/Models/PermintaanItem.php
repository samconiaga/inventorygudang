<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermintaanItem extends Model
{
    use HasFactory;

    protected $table = 'permintaan_items';

    protected $fillable = ['permintaan_id','barang_id','qty','satuan','note'];

    public function permintaan()
    {
        return $this->belongsTo(Permintaan::class, 'permintaan_id');
    }

    public function barang()
    {
        return $this->belongsTo(\App\Models\Barang::class, 'barang_id');
    }
}