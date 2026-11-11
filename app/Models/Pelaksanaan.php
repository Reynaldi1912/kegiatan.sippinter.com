<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pelaksanaan extends Model
{
    protected $fillable = [
        'kode_laporan',
        'client_sync_id',
        'penugasan_id',
        'kegiatan_id',
        'tupoksi_id',
        'pelapor_id',
        'tanggal_mulai',
        'tanggal_selesai',
        'uraian_hasil',
        'jumlah_target',
        'jumlah_realisasi',
        'satuan',
        'keterangan',
        'catatan_pegawai',
        'status_laporan',
        'sumber_input',
        'status_sinkronisasi',
        'synced_at',
        'verified_by',
        'verified_at',
        'catatan_verifikasi',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
            'jumlah_target' => 'integer',
            'jumlah_realisasi' => 'integer',
            'synced_at' => 'datetime',
            'verified_at' => 'datetime',
        ];
    }

    public function penugasan(): BelongsTo
    {
        return $this->belongsTo(Penugasan::class);
    }

    public function kegiatan(): BelongsTo
    {
        return $this->belongsTo(Kegiatan::class);
    }

    public function tupoksi(): BelongsTo
    {
        return $this->belongsTo(Tupoksi::class);
    }

    public function pelapor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pelapor_id');
    }

    public function verifikator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function dokumentasis(): HasMany
    {
        return $this->hasMany(DokumentasiKegiatan::class);
    }
}
