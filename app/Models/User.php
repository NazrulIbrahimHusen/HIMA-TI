<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'nim',
        'password',
        'role',
        'password_changed',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'password_changed'  => 'boolean',
        ];
    }

    // Relasi ke anggota
    public function anggota()
    {
        return $this->hasOne(\App\Models\Anggota::class);
    }

    // Cek apakah user adalah super admin
    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    // Cek apakah user adalah admin
    public function isAdmin(): bool
    {
        return in_array($this->role, ['super_admin', 'admin']);
    }

    // Cek apakah user adalah mahasiswa
    public function isMahasiswa(): bool
    {
        return $this->role === 'mahasiswa';
    }
}
