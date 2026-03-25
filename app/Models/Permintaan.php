<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permintaan extends Model
{
    use HasFactory;

    protected $fillable = ['kode','user_id','note','status','approved_by','approved_at'];

    public function pemohon()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    public function approver()
    {
        return $this->belongsTo(\App\Models\User::class, 'approved_by');
    }

    public function items()
    {
        return $this->hasMany(PermintaanItem::class, 'permintaan_id');
    }
}