<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Supplier;
use App\Models\Customer;
use App\Models\User;
use App\Models\PurchaseOrderItem;
use App\Models\BarangMasuk;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $table = 'purchase_orders';

    protected $fillable = [
        'po_number',
        'supplier_id',
        'department_id',   // ini sekarang refer ke customers.id
        'estimate_date',
        'status',
        'created_by',
        'notes',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    // =========================
    // Supplier
    // =========================
    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    // =========================
    // Departemen (ambil dari customers)
    // =========================
    public function department()
    {
        return $this->belongsTo(Customer::class, 'department_id');
    }

    // =========================
    // User pembuat PO
    // =========================
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // =========================
    // Detail item PO
    // =========================
    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class, 'purchase_order_id');
    }

    // =========================
    // Relasi ke barang masuk
    // =========================
    public function barangMasuks()
    {
        return $this->hasMany(BarangMasuk::class, 'purchase_order_id');
    }
}