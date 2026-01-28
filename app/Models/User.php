<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

use Illuminate\Support\Str;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    // User.php

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(
            Project::class,
            'project_user_roles', // 👈 IMPORTANT
            'user_id',
            'project_id'
        )->withPivot(['role', 'is_active'])
            ->withTimestamps();
    }

    public function role(): ?string
    {
        return $this->projects()->first()?->pivot?->role;
    }


    public function hasRole(string|array $roles): bool
    {
        $role = $this->role();

        if (! $role) {
            return false;
        }

        return is_array($roles)
            ? in_array($role, $roles)
            : $role === $roles;
    }

    public function isProjectOwner(): bool
    {
        return $this->ownedProject !== null;
    }




    // User.php
    public function ownedProject(): HasOne
    {
        return $this->hasOne(Project::class, 'owner_id');
    }


    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn($word) => Str::substr($word, 0, 1))
            ->implode('');
    }
}
