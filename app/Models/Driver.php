<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Driver extends Model
{
    use SoftDeletes;

    protected $fillable = [
        "rut",
        "full_name",
        "phone",
        "email",
        "license_number",
        "license_class",
        "license_issue_date",
        "license_expiration_date",
        "license_file",
        "active",
        "observations",
    ];

    protected function casts(): array
    {
        return [
            "license_issue_date" => "date",
            "license_expiration_date" => "date",
            "active" => "boolean",
        ];
    }

    public function assignedVehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class, "current_driver_id");
    }

    public function maintenances(): HasMany
    {
        return $this->hasMany(Maintenance::class, "assigned_driver_id");
    }

    public function hasValidLicense(): bool
    {
        return $this->license_expiration_date && $this->license_expiration_date->isFuture();
    }

    public function hasExpiredLicense(): bool
    {
        return $this->license_expiration_date && $this->license_expiration_date->isPast();
    }

    public function licenseExpiringSoon(int $days = 30): bool
    {
        return $this->license_expiration_date
            && $this->license_expiration_date->isFuture()
            && $this->license_expiration_date->lte(now()->addDays($days));
    }
}
