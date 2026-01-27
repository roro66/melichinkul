<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VehicleCategory extends Model
{
    protected $fillable = [
        "name",
        "slug",
        "description",
        "main_counter",
        "default_criticality",
        "requires_certifications",
        "requires_special_certifications",
        "active",
    ];

    protected function casts(): array
    {
        return [
            "requires_certifications" => "boolean",
            "requires_special_certifications" => "boolean",
            "active" => "boolean",
        ];
    }

    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class, "category_id");
    }
}
