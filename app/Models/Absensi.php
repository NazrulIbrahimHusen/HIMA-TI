<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Absensi extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'absensi';

    protected $fillable = [
        'sesi_id',
        'anggota_id',
        'nim',
        'nama',
        'tipe_peserta',
        'status',
        'metode',
        'waktu_absen',
        'keterangan',
    ];

    protected $casts = [
        'waktu_absen' => 'datetime',
    ];

    // ── Relasi ──────────────────────────────────────────

    public function sesi()
    {
        return $this->belongsTo(SesiAbsensi::class, 'sesi_id');
    }

    public function anggota()
    {
        return $this->belongsTo(Anggota::class);
    }

    public function logs()
    {
        return $this->hasMany(AbsensiLog::class);
    }

    // ── Scopes ──────────────────────────────────────────

    public function scopeAnggota($query)
    {
        return $query->where('tipe_peserta', 'anggota');
    }

    public function scopeHadir($query)
    {
        return $query->where('status', 'hadir');
    }

    public function scopeSearch($query, $keyword)
    {
        return $query->where(function ($q) use ($keyword) {
            $q->where('nama', 'like', "%{$keyword}%")
              ->orWhere('nim', 'like', "%{$keyword}%");
        });
    }

    // ── Helpers ─────────────────────────────────────────

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'hadir' => 'Hadir',
            'izin'  => 'Izin',
            'sakit' => 'Sakit',
            'alpa'  => 'Alpa',
            default => '-',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'hadir' => 'green',
            'izin'  => 'blue',
            'sakit' => 'yellow',
            'alpa'  => 'red',
            default => 'gray',
        };
    }
}
