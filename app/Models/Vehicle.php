<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehicle extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'license_plate',
        'brand',
        'model',
        'year',
        'engine_number',
        'chassis_number',
        'rut_tramites',
        'rut_propietario',
        'tarjeta_combustible',
        'gps',
        'tire_size',
        'category_id',
        'fuel_type',
        'status',
        'current_mileage',
        'current_hours',
        'mileage_updated_at',
        'current_driver_id',
        'incorporation_date',
        'purchase_value',
        'observations',
        'safety_gata',
        'safety_llave_rueda',
        'safety_triangulo',
        'safety_botiquin',
        'safety_gancho_arrastre',
    ];

    protected function casts(): array
    {
        return [
            'incorporation_date' => 'date',
            'mileage_updated_at' => 'date',
            'current_mileage' => 'decimal:2',
            'current_hours' => 'decimal:2',
            'purchase_value' => 'integer',
            'tarjeta_combustible' => 'boolean',
            'gps' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(VehicleCategory::class, 'category_id');
    }

    public function currentDriver(): BelongsTo
    {
        return $this->belongsTo(Driver::class, 'current_driver_id');
    }

    public function certifications(): HasMany
    {
        return $this->hasMany(Certification::class, 'vehicle_id');
    }

    public function maintenances(): HasMany
    {
        return $this->hasMany(Maintenance::class, 'vehicle_id');
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(Alert::class, 'vehicle_id');
    }

    public function hasValidTechnicalReview(): bool
    {
        return $this->certifications()
            ->where('type', 'technical_review')
            ->where('required', true)
            ->where('active', true)
            ->where('expiration_date', '>', now())
            ->exists();
    }

    public function hasValidSOAP(): bool
    {
        return $this->certifications()
            ->where('type', 'soap')
            ->where('required', true)
            ->where('active', true)
            ->where('expiration_date', '>', now())
            ->exists();
    }
}
