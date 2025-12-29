<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'po_number',
        'supplier_id',
        'department_id',
        'estimate_date',
        'status',
        'created_by',
        'notes',
    ];

    // relasi ke supplier
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    // relasi ke departemen
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    // user yang membuat
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // detail item
    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    // relasi ke barang masuk (jika mau)
    public function barangMasuks()
    {
        return $this->hasMany(BarangMasuk::class);
    }
}
