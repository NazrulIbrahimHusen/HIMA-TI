<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class SesiAbsensi extends Model
{
    use HasFactory;

    protected $table = 'sesi_absensi';

    protected $fillable = [
        'kegiatan_id',
        'nama_sesi',
        'waktu_mulai',
        'waktu_selesai',
    ];

    protected $casts = [
        'waktu_mulai'   => 'datetime',
        'waktu_selesai' => 'datetime',
    ];

    // ── Relasi ──────────────────────────────────────────

    public function kegiatan()
    {
        return $this->belongsTo(Kegiatan::class);
    }

    public function absensi()
    {
        return $this->hasMany(Absensi::class, 'sesi_id');
    }

    // ── Helpers ─────────────────────────────────────────

    public function isAktif(): bool
    {
        $now = Carbon::now();
        return $now->between($this->waktu_mulai, $this->waktu_selesai);
    }

    public function isExpired(): bool
    {
        return Carbon::now()->isAfter($this->waktu_selesai);
    }

    public function isBelumMulai(): bool
    {
        return Carbon::now()->isBefore($this->waktu_mulai);
    }
}
