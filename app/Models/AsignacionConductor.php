<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AsignacionConductor extends Model
{
    protected $table = 'asignaciones_conductores';

    protected $fillable = [
        'conductor_id',
        'vehiculo_id',
        'fecha_asignacion',
        'fecha_fin',
        'asignado_por_id',
        'motivo_fin',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'fecha_asignacion' => 'date',
            'fecha_fin' => 'date',
        ];
    }

    public function conductor(): BelongsTo
    {
        return $this->belongsTo(Conductor::class, 'conductor_id');
    }

    public function vehiculo(): BelongsTo
    {
        return $this->belongsTo(Vehiculo::class, 'vehiculo_id');
    }

    public function asignadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'asignado_por_id');
    }

    public function estaActiva(): bool
    {
        return $this->fecha_fin === null;
    }
}
