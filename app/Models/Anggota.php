<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Anggota extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'anggota';

    protected $fillable = [
        'user_id',
        'nim',
        'nama',
        'prodi',
        'angkatan',
        'semester',
        'foto',
        'status',
    ];

    protected $casts = [
        'angkatan' => 'integer',
        'semester' => 'integer',
    ];

    // ── Relasi ──────────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pengurus()
    {
        return $this->hasMany(Pengurus::class);
    }

    /**
     * Jabatan pengurus pada periode yang sedang aktif (null = hanya anggota biasa).
     */
    public function pengurusAktif()
    {
        return $this->hasOne(Pengurus::class)
            ->whereHas('periode', fn ($q) => $q->where('status', 'aktif'));
    }

    public function absensi()
    {
        return $this->hasMany(Absensi::class);
    }

    // ── Scopes ──────────────────────────────────────────

    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }

    public function scopeSearch($query, $keyword)
    {
        return $query->where(function ($q) use ($keyword) {
            $q->where('nama', 'like', "%{$keyword}%")
              ->orWhere('nim', 'like', "%{$keyword}%")
              ->orWhere('prodi', 'like', "%{$keyword}%");
        });
    }

    // ── Accessors ────────────────────────────────────────

    public function getFotoUrlAttribute(): string
    {
        return $this->foto
            ? asset('storage/' . $this->foto)
            : asset('images/default-avatar.png');
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->status === 'aktif' ? 'Aktif' : 'Tidak Aktif';
    }

    /**
     * Label jabatan untuk KTA/profil, mengikuti periode aktif.
     * Contoh: "Anggota", "Ketua Umum", "Koordinator Humas".
     */
    public function getJabatanLabelAttribute(): string
    {
        $p = $this->pengurusAktif;

        if (!$p) {
            return 'Anggota';
        }

        return $p->divisi ? "{$p->jabatan} {$p->divisi->nama}" : $p->jabatan;
    }
}