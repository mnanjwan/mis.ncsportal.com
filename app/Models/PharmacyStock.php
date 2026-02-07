<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PharmacyStock extends Model
{
    use HasFactory;

    protected $fillable = [
        'pharmacy_drug_id',
        'location_type',
        'command_id',
        'quantity',
        'expiry_date',
        'batch_number',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'expiry_date' => 'date',
        ];
    }

    // Relationships
    public function drug()
    {
        return $this->belongsTo(PharmacyDrug::class, 'pharmacy_drug_id');
    }

    public function command()
    {
        return $this->belongsTo(Command::class);
    }

    // Scopes
    public function scopeCentralStore($query)
    {
        return $query->where('location_type', 'CENTRAL_STORE');
    }

    public function scopeCommandPharmacy($query)
    {
        return $query->where('location_type', 'COMMAND_PHARMACY');
    }

    public function scopeByCommand($query, $commandId)
    {
        return $query->where('command_id', $commandId);
    }

    public function scopeExpiringSoon($query, $days = 90)
    {
        return $query->whereNotNull('expiry_date')
            ->where('expiry_date', '<=', now()->addDays($days))
            ->where('expiry_date', '>=', now());
    }

    public function scopeExpired($query)
    {
        return $query->whereNotNull('expiry_date')
            ->where('expiry_date', '<', now());
    }

    public function scopeWithStock($query)
    {
        return $query->where('quantity', '>', 0);
    }

    // Helper methods
    public function isCentralStore(): bool
    {
        return $this->location_type === 'CENTRAL_STORE';
    }

    public function isCommandPharmacy(): bool
    {
        return $this->location_type === 'COMMAND_PHARMACY';
    }

    public function isExpired(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    public function isExpiringSoon($days = 90): bool
    {
        if (!$this->expiry_date) {
            return false;
        }
        return $this->expiry_date->between(now(), now()->addDays($days));
    }

    public function isExpiringVerySoon($days = 30): bool
    {
        if (!$this->expiry_date) {
            return false;
        }
        return $this->expiry_date->between(now(), now()->addDays($days));
    }

    public function isExpiringModerately($days = 60): bool
    {
        if (!$this->expiry_date) {
            return false;
        }
        return $this->expiry_date->between(now()->addDays(30), now()->addDays($days));
    }

    public function getDaysUntilExpiry(): ?int
    {
        if (!$this->expiry_date) {
            return null;
        }
        return now()->diffInDays($this->expiry_date, false);
    }

    public function getExpiryWarningLevel(): string
    {
        if ($this->isExpired()) {
            return 'expired';
        }
        if ($this->isExpiringVerySoon(30)) {
            return 'critical';
        }
        if ($this->isExpiringModerately(60)) {
            return 'warning';
        }
        if ($this->isExpiringSoon(90)) {
            return 'caution';
        }
        return 'ok';
    }

    public function getExpiryWarningBadge(): string
    {
        $level = $this->getExpiryWarningLevel();
        $days = $this->getDaysUntilExpiry();
        
        switch ($level) {
            case 'expired':
                return '<span class="kt-badge kt-badge-danger kt-badge-sm">Expired</span>';
            case 'critical':
                return '<span class="kt-badge kt-badge-danger kt-badge-sm">Expires in ' . $days . ' days</span>';
            case 'warning':
                return '<span class="kt-badge kt-badge-warning kt-badge-sm">Expires in ' . $days . ' days</span>';
            case 'caution':
                return '<span class="kt-badge kt-badge-info kt-badge-sm">Expires in ' . $days . ' days</span>';
            default:
                return '';
        }
    }

    public function getLocationName(): string
    {
        if ($this->isCentralStore()) {
            return 'Central Medical Store';
        }
        return $this->command?->name ?? 'Command Pharmacy';
    }
}
