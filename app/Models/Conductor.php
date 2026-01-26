<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Conductor extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'rut',
        'nombre_completo',
        'telefono',
        'email',
        'licencia_numero',
        'licencia_clase',
        'licencia_fecha_emision',
        'licencia_vencimiento',
        'licencia_archivo',
        'activo',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'licencia_fecha_emision' => 'date',
            'licencia_vencimiento' => 'date',
            'activo' => 'boolean',
        ];
    }

    public function vehiculosAsignados(): HasMany
    {
        return $this->hasMany(Vehiculo::class, 'conductor_actual_id');
    }

    public function asignaciones(): HasMany
    {
        return $this->hasMany(AsignacionConductor::class, 'conductor_id');
    }

    public function mantenimientos(): HasMany
    {
        return $this->hasMany(Mantenimiento::class, 'conductor_asignado_id');
    }

    public function alertas(): HasMany
    {
        return $this->hasMany(Alerta::class, 'conductor_id');
    }

    public function licenciaVigente(): bool
    {
        return $this->licencia_vencimiento && $this->licencia_vencimiento->isFuture();
    }

    public function licenciaVencida(): bool
    {
        return $this->licencia_vencimiento && $this->licencia_vencimiento->isPast();
    }

    public function licenciaPorVencer(int $dias = 30): bool
    {
        return $this->licencia_vencimiento
            && $this->licencia_vencimiento->isFuture()
            && $this->licencia_vencimiento->lte(now()->addDays($dias));
    }
}
