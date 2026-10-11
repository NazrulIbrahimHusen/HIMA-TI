<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pengumuman extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pengumuman';

    protected $fillable = [
        'judul',
        'konten',
        'posted_by',
        'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    // ── Relasi ──────────────────────────────────────────

    public function postedBy()
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    // ── Scopes ──────────────────────────────────────────

    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at')
                     ->where('published_at', '<=', now());
    }

    public function scopeDraft($query)
    {
        return $query->whereNull('published_at');
    }

    // ── Helpers ─────────────────────────────────────────

    public function isPublished(): bool
    {
        return !is_null($this->published_at) && $this->published_at->isPast();
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->isPublished() ? 'Published' : 'Draft';
    }
}
