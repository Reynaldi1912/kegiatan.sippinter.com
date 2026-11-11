<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotifikasiPegawai extends Model
{
    protected $fillable = [
        'user_id',
        'penugasan_id',
        'kegiatan_id',
        'pelaksanaan_id',
        'jenis_notifikasi',
        'judul',
        'pesan',
        'url_tujuan',
        'is_read',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
            'read_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function penugasan(): BelongsTo
    {
        return $this->belongsTo(Penugasan::class);
    }

    public function kegiatan(): BelongsTo
    {
        return $this->belongsTo(Kegiatan::class);
    }

    public function pelaksanaan(): BelongsTo
    {
        return $this->belongsTo(Pelaksanaan::class);
    }
}
