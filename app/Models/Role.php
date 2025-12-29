<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    // boleh pakai guarded seperti ini:
    protected $guarded = ['id'];
    // atau kalau mau lebih eksplisit:
    // protected $fillable = ['role', 'deskripsi', 'department_id'];

    // 1 Role dimiliki banyak User
    public function users()
    {
        // foreign key di tabel users adalah role_id (bukan user_id)
        return $this->hasMany(User::class, 'role_id');
    }

    // 1 Role dimiliki oleh 1 Departemen (optional)
    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }
}
