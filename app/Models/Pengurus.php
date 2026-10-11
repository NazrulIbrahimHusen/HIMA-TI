<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pengurus extends Model
{
    use HasFactory;

    protected $table = 'pengurus';

    protected $fillable = [
        'anggota_id',
        'periode_id',
        'divisi_id',
        'jabatan',
        'urutan',
    ];

    protected $casts = [
        'urutan' => 'integer',
    ];

    // ── Relasi ──────────────────────────────────────────

    public function anggota()
    {
        return $this->belongsTo(Anggota::class);
    }

    public function periode()
    {
        return $this->belongsTo(PeriodeKepengurusan::class, 'periode_id');
    }

    public function divisi()
    {
        return $this->belongsTo(Divisi::class);
    }

    // ── Scopes ──────────────────────────────────────────

    public function scopeUrut($query)
    {
        return $query->orderBy('urutan');
    }

    public function scopePeriodeAktif($query)
    {
        return $query->whereHas('periode', fn($q) => $q->where('status', 'aktif'));
    }
}
