<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'fullname',
        'username',
        'nip',
        'email',
        'jabatan',
        'unit_program',
        'phone',
        'avatar',
        'role',
        'is_active',
        'password',
        'api_token',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'api_token',
    ];

    protected $appends = [
        'name',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Display name mapped onto the shared SIPPINTER `fullname` column.
     *
     * Reading falls back to `username` when `fullname` is empty; writing `name`
     * stores the value in `fullname`.
     */
    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes): string => ($attributes['fullname'] ?? null) ?: ($attributes['username'] ?? ''),
            set: fn (?string $value): array => ['fullname' => $value],
        );
    }

    /**
     * Store passwords using MD5 to match the shared `sippinter_new.users` format.
     * If an already-hashed MD5 (32 hex chars) or Bcrypt/Argon2 string is passed,
     * it is stored as-is without double-hashing.
     */
    protected function password(): Attribute
    {
        return Attribute::make(
            set: function (?string $value): ?string {
                if ($value === null || $value === '') {
                    return $value;
                }

                if (preg_match('/^[a-f0-9]{32}$/i', $value) === 1 || str_starts_with($value, '$2y$') || str_starts_with($value, '$2a$') || str_starts_with($value, '$argon2')) {
                    return $value;
                }

                return md5($value);
            },
        );
    }

    /**
     * Verify a plain-text password against the stored MD5 hash (or legacy Bcrypt/Argon2 hash).
     */
    public function verifyPassword(string $plainPassword): bool
    {
        $stored = (string) $this->password;

        if ($stored === '') {
            return false;
        }

        if (hash_equals(strtolower($stored), md5($plainPassword))) {
            return true;
        }

        if (str_starts_with($stored, '$2y$') || str_starts_with($stored, '$2a$') || str_starts_with($stored, '$argon2')) {
            return Hash::check($plainPassword, $stored);
        }

        return hash_equals($stored, $plainPassword);
    }

    /**
     * Check if user has admin/verifier privileges.
     */
    public function isVerifier(): bool
    {
        return in_array($this->role, [
            'super_admin',
            'admin',
            'adminkes',
            'dokter',
            'kepala_puskesmas',
            'kepala_tata_usaha',
            'pj_program',
        ], true);
    }

    public function tupoksis(): HasMany
    {
        return $this->hasMany(Tupoksi::class);
    }

    public function kegiatansPenanggungJawab(): HasMany
    {
        return $this->hasMany(Kegiatan::class, 'penanggung_jawab_id');
    }

    public function penugasanDiberikan(): HasMany
    {
        return $this->hasMany(Penugasan::class, 'pemberi_tugas_id');
    }

    public function penugasanUtama(): HasMany
    {
        return $this->hasMany(Penugasan::class, 'penanggung_jawab_id');
    }

    public function penugasans(): BelongsToMany
    {
        return $this->belongsToMany(Penugasan::class, 'penugasan_anggota')
            ->withPivot('peran')
            ->withTimestamps();
    }

    public function pelaksanaans(): HasMany
    {
        return $this->hasMany(Pelaksanaan::class, 'pelapor_id');
    }

    public function notifikasis(): HasMany
    {
        return $this->hasMany(NotifikasiPegawai::class);
    }
}
