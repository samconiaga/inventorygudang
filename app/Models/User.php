<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Field yang boleh diisi
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'department_id',
    ];

    /**
     * Field tersembunyi
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Casting field
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /* =====================================================
     | RELATION
     ===================================================== */

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    /* =====================================================
     | HELPER ROLE (BIAR CONTROLLER & VIEW RAPI)
     ===================================================== */

    public function isSuperAdmin(): bool
    {
        return optional($this->role)->role === 'superadmin';
    }

    public function isAdminGudang(): bool
    {
        return optional($this->role)->role === 'admin gudang';
    }

    public function isKepalaGudang(): bool
    {
        return optional($this->role)->role === 'kepala gudang';
    }

    /* =====================================================
     | NOTIFICATION HELPER
     ===================================================== */

    /**
     * Ambil unread notifications (shortcut)
     */
    public function unreadNotif()
    {
        return $this->unreadNotifications();
    }

    /**
     * Ambil semua notifications (shortcut)
     */
    public function allNotif()
    {
        return $this->notifications();
    }
}
