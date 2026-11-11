<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApprovalLine extends Model
{
    protected $fillable = [
        'kode_aturan',
        'nama_aturan',
        'modul',
        'deskripsi',
        'nominal_minimal',
        'nominal_maksimal',
        'step_order',
        'nama_level',
        'approver_type',
        'approver_role',
        'approver_user_id',
        'allow_web',
        'allow_mobile',
        'is_mandatory',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'nominal_minimal' => 'decimal:2',
            'nominal_maksimal' => 'decimal:2',
            'step_order' => 'integer',
            'allow_web' => 'boolean',
            'allow_mobile' => 'boolean',
            'is_mandatory' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function approverUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_user_id');
    }

    public function steps(): HasMany
    {
        return $this->hasMany(ApprovalRequestStep::class);
    }

    public function getLabelModulAttribute(): string
    {
        return match ($this->modul) {
            'pencairan_dana' => 'Pencairan Dana (pencairan_danas)',
            'kegiatan_rpk' => 'Rencana Anggaran RPK / BOK (budget_plans)',
            'pelaksanaan_spj' => 'SPJ & Kuitansi BOK (bok_spjs / pelaksanaans)',
            'penugasan' => 'Penugasan & Kegiatan Lapangan (penugasans)',
            default => ucfirst(str_replace('_', ' ', $this->modul)),
        };
    }

    public function getNamaTabelDatabaseAttribute(): string
    {
        return match ($this->modul) {
            'pencairan_dana' => 'pencairan_danas, approval_requests',
            'kegiatan_rpk' => 'budget_plans, budget_plan_histories, kegiatans',
            'pelaksanaan_spj' => 'bok_spjs, bok_spj_details, pelaksanaans',
            'penugasan' => 'penugasans, bok_activities, bok_participants',
            default => 'approval_requests',
        };
    }
}
