<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AbsensiLog extends Model
{
    use HasFactory;

    protected $table = 'absensi_log';

    public $timestamps = false;

    protected $fillable = [
        'absensi_id',
        'changed_by',
        'status_lama',
        'status_baru',
        'catatan',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    // ── Relasi ──────────────────────────────────────────

    public function absensi()
    {
        return $this->belongsTo(Absensi::class);
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
