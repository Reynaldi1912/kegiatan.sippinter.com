<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Penugasan extends Model
{
    protected $fillable = [
        'nomor_surat_tugas',
        'kegiatan_id',
        'tupoksi_id',
        'pemberi_tugas_id',
        'penanggung_jawab_id',
        'instruksi_pekerjaan',
        'sumber_kegiatan',
        'prioritas',
        'tanggal_mulai',
        'tenggat_waktu',
        'target_pekerjaan',
        'satuan',
        'indikator_keberhasilan',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'date',
            'tenggat_waktu' => 'date',
            'target_pekerjaan' => 'integer',
        ];
    }

    public function kegiatan(): BelongsTo
    {
        return $this->belongsTo(Kegiatan::class);
    }

    public function tupoksi(): BelongsTo
    {
        return $this->belongsTo(Tupoksi::class);
    }

    public function pemberiTugas(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pemberi_tugas_id');
    }

    public function penanggungJawab(): BelongsTo
    {
        return $this->belongsTo(User::class, 'penanggung_jawab_id');
    }

    public function anggotaPelaksana(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'penugasan_anggota')
            ->withPivot('peran')
            ->withTimestamps();
    }

    public function pelaksanaans(): HasMany
    {
        return $this->hasMany(Pelaksanaan::class);
    }

    public function dokumentasis(): HasMany
    {
        return $this->hasMany(DokumentasiKegiatan::class);
    }
}
