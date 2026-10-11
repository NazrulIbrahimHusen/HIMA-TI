<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PeriodeKepengurusan extends Model
{
    use HasFactory;

    protected $table = 'periode_kepengurusan';

    protected $fillable = [
        'nama',
        'tahun_mulai',
        'tahun_selesai',
        'status',
    ];

    protected $casts = [
        'tahun_mulai'   => 'integer',
        'tahun_selesai' => 'integer',
    ];

    // ── Relasi ──────────────────────────────────────────

    public function divisi()
    {
        return $this->hasMany(Divisi::class, 'periode_id');
    }

    public function pengurus()
    {
        return $this->hasMany(Pengurus::class, 'periode_id');
    }

    // ── Scopes ──────────────────────────────────────────

    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }

    // ── Static Helpers ───────────────────────────────────

    public static function getAktif(): ?self
    {
        return static::where('status', 'aktif')->first();
    }
}
