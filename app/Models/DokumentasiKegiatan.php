<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DokumentasiKegiatan extends Model
{
    protected $fillable = [
        'pelaksanaan_id',
        'penugasan_id',
        'uploaded_by',
        'jenis_dokumen',
        'nama_file',
        'file_path',
        'mime_type',
        'ukuran_kb',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'ukuran_kb' => 'integer',
        ];
    }

    public function pelaksanaan(): BelongsTo
    {
        return $this->belongsTo(Pelaksanaan::class);
    }

    public function penugasan(): BelongsTo
    {
        return $this->belongsTo(Penugasan::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
