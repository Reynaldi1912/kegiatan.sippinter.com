<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kegiatan extends Model
{
    protected $fillable = [
        'kode_kegiatan',
        'nama_kegiatan',
        'deskripsi',
        'program',
        'jenis_dokumen',
        'sumber_dana',
        'sumber_kegiatan',
        'kode_rekening',
        'pagu_anggaran',
        'realisasi_anggaran',
        'status_keuangan',
        'tanggal_mulai',
        'tanggal_selesai',
        'waktu_mulai',
        'waktu_selesai',
        'lokasi',
        'penanggung_jawab_id',
        'target_kegiatan',
        'satuan_target',
        'indikator_keberhasilan',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'pagu_anggaran' => 'decimal:2',
            'realisasi_anggaran' => 'decimal:2',
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
            'target_kegiatan' => 'integer',
        ];
    }

    public function penanggungJawab(): BelongsTo
    {
        return $this->belongsTo(User::class, 'penanggung_jawab_id');
    }

    public function rekening(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'kode_rekening', 'kode_akun');
    }

    public function penugasans(): HasMany
    {
        return $this->hasMany(Penugasan::class);
    }

    public function pelaksanaans(): HasMany
    {
        return $this->hasMany(Pelaksanaan::class);
    }
}
