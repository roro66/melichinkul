<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehiculo extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'patente',
        'marca',
        'modelo',
        'anio',
        'numero_motor',
        'numero_chasis',
        'categoria_id',
        'tipo_combustible',
        'estado',
        'kilometraje_actual',
        'horometro_actual',
        'conductor_actual_id',
        'fecha_incorporacion',
        'valor_compra',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'fecha_incorporacion' => 'date',
            'kilometraje_actual' => 'decimal:2',
            'horometro_actual' => 'decimal:2',
            'valor_compra' => 'integer',
        ];
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(CategoriaVehiculo::class, 'categoria_id');
    }

    public function conductorActual(): BelongsTo
    {
        return $this->belongsTo(Conductor::class, 'conductor_actual_id');
    }

    public function certificaciones(): HasMany
    {
        return $this->hasMany(Certificacion::class, 'vehiculo_id');
    }

    public function mantenimientos(): HasMany
    {
        return $this->hasMany(Mantenimiento::class, 'vehiculo_id');
    }

    public function asignaciones(): HasMany
    {
        return $this->hasMany(AsignacionConductor::class, 'vehiculo_id');
    }

    public function alertas(): HasMany
    {
        return $this->hasMany(Alerta::class, 'vehiculo_id');
    }

    public function revisionTecnicaVigente(): bool
    {
        $rt = $this->certificaciones()
            ->where('tipo', 'revision_tecnica')
            ->where('obligatorio', true)
            ->where('activo', true)
            ->where('fecha_vencimiento', '>', now())
            ->exists();

        return $rt;
    }

    public function soapVigente(): bool
    {
        return $this->certificaciones()
            ->where('tipo', 'soap')
            ->where('obligatorio', true)
            ->where('activo', true)
            ->where('fecha_vencimiento', '>', now())
            ->exists();
    }
}
