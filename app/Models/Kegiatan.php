<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Kegiatan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'kegiatan';

    protected $fillable = [
        'nama',
        'deskripsi',
        'tanggal_mulai',
        'tanggal_selesai',
        'lokasi',
        'status',
        'tipe',
        'token',
        'sertifikat_aktif',
        'sertifikat_url',
    ];

    protected $casts = [
        'tanggal_mulai'    => 'date',
        'tanggal_selesai'  => 'date',
        'sertifikat_aktif' => 'boolean',
    ];

    // ── Boot: auto-generate token UUID ──────────────────

    protected static function booted(): void
    {
        static::creating(function ($kegiatan) {
            if (empty($kegiatan->token)) {
                $kegiatan->token = (string) Str::uuid();
            }
        });
    }

    // ── Relasi ──────────────────────────────────────────

    public function sesiAbsensi()
    {
        return $this->hasMany(SesiAbsensi::class);
    }

    // ── Scopes ──────────────────────────────────────────

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopePublik($query)
    {
        return $query->whereIn('status', ['published', 'closed']);
    }

    public function scopeSearch($query, $keyword)
    {
        return $query->where(function ($q) use ($keyword) {
            $q->where('nama', 'like', "%{$keyword}%")
              ->orWhere('lokasi', 'like', "%{$keyword}%");
        });
    }

    // ── Helpers ─────────────────────────────────────────

    public function isDeletable(): bool
    {
        return !$this->sesiAbsensi()->whereHas('absensi')->exists();
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft'     => 'Draft',
            'published' => 'Published',
            'closed'    => 'Selesai',
            default     => '-',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'draft'     => 'gray',
            'published' => 'green',
            'closed'    => 'red',
            default     => 'gray',
        };
    }
}
