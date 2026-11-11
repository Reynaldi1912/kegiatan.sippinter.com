<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChartOfAccount extends Model
{
    use HasFactory;

    protected $table = 'chart_of_accounts';

    protected $fillable = [
        'kode_akun',
        'nama_akun',
        'kategori',
        'level',
        'parent_kode',
        'is_active',
    ];

    protected $casts = [
        'level' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Parent account
     */
    public function parent()
    {
        return $this->belongsTo(ChartOfAccount::class, 'parent_kode', 'kode_akun');
    }

    /**
     * Children accounts
     */
    public function children()
    {
        return $this->hasMany(ChartOfAccount::class, 'parent_kode', 'kode_akun');
    }
}
