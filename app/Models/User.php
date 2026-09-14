<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'phone', 'role', 'locale', 'is_active',
    ];

    protected $hidden = [
        'password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'two_factor_secret' => 'encrypted',
            'two_factor_recovery_codes' => 'array',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    public function isAdmin(): bool { return $this->role === 'admin'; }
    public function isOwner(): bool { return $this->role === 'owner'; }
    public function isTenant(): bool { return $this->role === 'tenant'; }

    // Recipients for admin notifications
    public static function activeAdmins()
    {
        return static::where('role', 'admin')->where('is_active', true);
    }

    public function hasTwoFactorEnabled(): bool
    {
        return $this->two_factor_secret !== null && $this->two_factor_confirmed_at !== null;
    }

    // Admins handle money, contracts and accounts, so a second factor is mandatory for them
    public function mustUseTwoFactor(): bool
    {
        return $this->isAdmin();
    }

    public function clearTwoFactor(): void
    {
        $this->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
            'two_factor_last_used_step' => null,
        ])->save();
    }

    // Properties this user owns (role = owner)
    public function properties()
    {
        return $this->hasMany(Property::class, 'owner_id');
    }

    // Leases this user holds as a tenant
    public function leases()
    {
        return $this->hasMany(Lease::class, 'tenant_id');
    }

    public function reportedIssues()
    {
        return $this->hasMany(Issue::class, 'reported_by');
    }

    public function assignedIssues()
    {
        return $this->hasMany(Issue::class, 'assigned_to');
    }

    public function handledVisitRequests()
    {
        return $this->hasMany(VisitRequest::class, 'handled_by');
    }
}
