<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'username' => fake()->unique()->userName(),
            'nip' => fake()->unique()->numerify('19##########00##'),
            'email' => fake()->unique()->safeEmail(),
            'jabatan' => 'Petugas Pelaksana Puskesmas',
            'unit_program' => 'UKM Esensial',
            'phone' => fake()->phoneNumber(),
            'role' => 'pegawai',
            'is_active' => true,
            'email_verified_at' => now(),
            'password' => static::$password ??= md5('password'),
            'remember_token' => Str::random(10),
            'api_token' => 'sipp-mob-'.Str::random(32),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
