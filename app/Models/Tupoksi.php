<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tupoksi extends Model
{
    protected $fillable = [
        'kode_tupoksi',
        'user_id',
        'nama_tupoksi',
        'uraian_tugas',
        'target',
        'realisasi',
        'satuan',
        'indikator_keberhasilan',
        'periode',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'target' => 'integer',
            'realisasi' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function penugasans(): HasMany
    {
        return $this->hasMany(Penugasan::class);
    }

    public function pelaksanaans(): HasMany
    {
        return $this->hasMany(Pelaksanaan::class);
    }

    public function getPersentaseCapaianAttribute(): float
    {
        if ($this->target <= 0) {
            return 0.0;
        }

        return round(min(100, ($this->realisasi / $this->target) * 100), 1);
    }

    /**
     * Recalculate verified realization from approved Pelaksanaan reports.
     */
    public function recalculateRealisasi(): void
    {
        $totalVerified = (int) $this->pelaksanaans()
            ->where('status_laporan', 'disetujui')
            ->sum('jumlah_realisasi');

        $this->update(['realisasi' => $totalVerified]);
    }
}
