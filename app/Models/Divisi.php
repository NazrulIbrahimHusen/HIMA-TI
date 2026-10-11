<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Divisi extends Model
{
    use HasFactory;

    protected $table = 'divisi';

    protected $fillable = [
        'periode_id',
        'nama',
        'deskripsi',
        'urutan',
    ];

    protected $casts = [
        'urutan' => 'integer',
    ];

    // ── Relasi ──────────────────────────────────────────

    public function periode()
    {
        return $this->belongsTo(PeriodeKepengurusan::class, 'periode_id');
    }

    public function pengurus()
    {
        return $this->hasMany(Pengurus::class);
    }

    // ── Scopes ──────────────────────────────────────────

    public function scopeUrut($query)
    {
        return $query->orderBy('urutan');
    }

    // ── Helpers ─────────────────────────────────────────

    public function hasPengurus(): bool
    {
        return $this->pengurus()->exists();
    }
}
