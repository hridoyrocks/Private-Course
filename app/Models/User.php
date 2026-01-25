<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'max_devices',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
            'is_active' => 'boolean',
            'max_devices' => 'integer',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class)
            ->withPivot('expires_at')
            ->withTimestamps();
    }

    public function activeCourses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class)
            ->withPivot('expires_at')
            ->wherePivot('expires_at', '>', now())
            ->orWherePivot('expires_at', null)
            ->where('is_active', true)
            ->withTimestamps();
    }

    public function devices(): HasMany
    {
        return $this->hasMany(UserDevice::class);
    }

    public function hasAccessToCourse(Course $course): bool
    {
        $access = $this->courses()->where('course_id', $course->id)->first();

        if (!$access) {
            return false;
        }

        if ($access->pivot->expires_at === null) {
            return true;
        }

        return $access->pivot->expires_at > now();
    }

    public function canAddDevice(): bool
    {
        return $this->devices()->count() < $this->max_devices;
    }
}
