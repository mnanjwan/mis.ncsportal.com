<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use PragmaRX\Google2FA\Google2FA;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'email',
        'password',
        'temp_password',
        'is_active',
        'last_login',
        'current_session_id',
        'created_by',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login' => 'datetime',
            'two_factor_confirmed_at' => 'datetime',
            'two_factor_recovery_codes' => 'array',
        ];
    }

    // Relationships
    public function officer()
    {
        return $this->hasOne(Officer::class);
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles')
            ->withPivot('command_id', 'assigned_at', 'assigned_by', 'is_active')
            ->withTimestamps();
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function createdUsers()
    {
        return $this->hasMany(User::class, 'created_by');
    }

    public function emolumentAssessments()
    {
        return $this->hasMany(EmolumentAssessment::class, 'assessor_id');
    }

    public function emolumentValidations()
    {
        return $this->hasMany(EmolumentValidation::class, 'validator_id');
    }

    public function leaveApprovals()
    {
        return $this->hasMany(LeaveApproval::class, 'staff_officer_id');
    }

    public function passApprovals()
    {
        return $this->hasMany(PassApproval::class, 'staff_officer_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }

    /**
     * Check if user has a specific role
     */
    public function hasRole($roleName): bool
    {
        return $this->roles()
            ->where('name', $roleName)
            ->wherePivot('is_active', true)
            ->exists();
    }

    /**
     * Check if user has any of the given roles
     */
    public function hasAnyRole(array $roleNames): bool
    {
        return $this->roles()
            ->whereIn('name', $roleNames)
            ->wherePivot('is_active', true)
            ->exists();
    }

    /**
     * Check if user is an officer
     */
    public function isOfficer(): bool
    {
        return $this->officer()->exists();
    }

    /**
     * Get the officer record associated with this user
     */
    public function getOfficer(): ?Officer
    {
        return $this->officer;
    }

    /**
     * Check if user has a specific role OR is an officer
     * This allows officers with roles to access both officer and role-specific features
     */
    public function hasRoleOrIsOfficer($roleName): bool
    {
        return $this->hasRole($roleName) || $this->isOfficer();
    }

    /**
     * Check if user has any of the given roles OR is an officer
     */
    public function hasAnyRoleOrIsOfficer(array $roleNames): bool
    {
        return $this->hasAnyRole($roleNames) || $this->isOfficer();
    }

    /**
     * Check if user can access officer features
     * Returns true if user is an officer (regardless of roles)
     */
    public function canAccessOfficerFeatures(): bool
    {
        return $this->isOfficer();
    }

    /**
     * Check if user can access role-specific features
     * Returns true if user has the required role (and they may also be an officer)
     */
    public function canAccessRoleFeatures($roleName): bool
    {
        return $this->hasRole($roleName);
    }

    /**
     * Check if user can access role-specific features (any of the given roles)
     */
    public function canAccessAnyRoleFeatures(array $roleNames): bool
    {
        return $this->hasAnyRole($roleNames);
    }

    /**
     * Check if 2FA is enabled for the user
     */
    public function hasTwoFactorEnabled(): bool
    {
        return !is_null($this->two_factor_secret) && !is_null($this->two_factor_confirmed_at);
    }

    /**
     * Generate a new 2FA secret
     */
    public function generateTwoFactorSecret(): string
    {
        $google2fa = new Google2FA();
        return $google2fa->generateSecretKey();
    }

    /**
     * Generate QR code data URL for 2FA setup
     */
    public function getTwoFactorQrCodeUrl(): string
    {
        $google2fa = new Google2FA();
        $companyName = config('app.name', 'NCS Employee Portal');
        $companyEmail = $this->email;
        
        return $google2fa->getQRCodeUrl(
            $companyName,
            $companyEmail,
            $this->two_factor_secret
        );
    }

    /**
     * Verify a 2FA code
     */
    public function verifyTwoFactorCode(string $code): bool
    {
        if (!$this->two_factor_secret) {
            return false;
        }

        $google2fa = new Google2FA();
        return $google2fa->verifyKey($this->two_factor_secret, $code);
    }

    /**
     * Generate recovery codes
     */
    public function generateRecoveryCodes(): array
    {
        return collect(range(1, 8))->map(function () {
            return str()->random(10);
        })->all();
    }

    /**
     * Check if a recovery code is valid and remove it
     */
    public function useRecoveryCode(string $code): bool
    {
        $recoveryCodes = $this->two_factor_recovery_codes ?? [];
        
        if (!in_array($code, $recoveryCodes)) {
            return false;
        }

        $recoveryCodes = array_values(array_diff($recoveryCodes, [$code]));
        $this->update(['two_factor_recovery_codes' => $recoveryCodes]);

        return true;
    }
}
