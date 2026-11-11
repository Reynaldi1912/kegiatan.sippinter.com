<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApprovalRequest extends Model
{
    protected $fillable = [
        'nomor_pengajuan',
        'judul_pengajuan',
        'modul',
        'referensi_tipe',
        'referensi_id',
        'kegiatan_id',
        'penugasan_id',
        'pelaksanaan_id',
        'pengaju_id',
        'nominal_diajukan',
        'nominal_disetujui',
        'sumber_dana',
        'kode_rekening',
        'uraian_pengajuan',
        'current_step',
        'total_steps',
        'status',
        'sumber_pengajuan',
        'submitted_at',
        'completed_at',
        'catatan_akhir',
    ];

    protected function casts(): array
    {
        return [
            'nominal_diajukan' => 'decimal:2',
            'nominal_disetujui' => 'decimal:2',
            'current_step' => 'integer',
            'total_steps' => 'integer',
            'submitted_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function kegiatan(): BelongsTo
    {
        return $this->belongsTo(Kegiatan::class);
    }

    public function penugasan(): BelongsTo
    {
        return $this->belongsTo(Penugasan::class);
    }

    public function pelaksanaan(): BelongsTo
    {
        return $this->belongsTo(Pelaksanaan::class);
    }

    public function pengaju(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pengaju_id');
    }

    public function steps(): HasMany
    {
        return $this->hasMany(ApprovalRequestStep::class)->orderBy('step_order');
    }

    public function activeStep(): ?ApprovalRequestStep
    {
        return $this->steps
            ->where('step_order', $this->current_step)
            ->where('status', 'menunggu')
            ->first()
            ?? $this->steps->where('status', 'menunggu')->sortBy('step_order')->first();
    }

    public function getLabelModulAttribute(): string
    {
        return match ($this->modul) {
            'pencairan_dana' => 'Pencairan Dana Kegiatan',
            'kegiatan_rpk' => 'Anggaran RUK / RPK / RBA',
            'pelaksanaan_spj' => 'SPJ & Realisasi Pelaksanaan',
            'penugasan' => 'Surat Tugas & Biaya Perjalanan',
            default => ucfirst(str_replace('_', ' ', $this->modul)),
        };
    }
}
