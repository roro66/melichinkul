<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Mantenimiento extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'vehiculo_id',
        'tipo',
        'estado',
        'fecha_programada',
        'fecha_inicio',
        'fecha_fin',
        'kilometraje_en_mantenimiento',
        'horometro_en_mantenimiento',
        'motivo_ingreso',
        'descripcion_trabajo',
        'trabajos_realizados',
        'costo_repuestos',
        'costo_mano_obra',
        'costo_total',
        'horas_trabajadas',
        'taller_proveedor',
        'tecnico_responsable_id',
        'conductor_asignado_id',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'fecha_programada' => 'date',
            'fecha_inicio' => 'date',
            'fecha_fin' => 'date',
            'kilometraje_en_mantenimiento' => 'decimal:2',
            'horometro_en_mantenimiento' => 'decimal:2',
            'horas_trabajadas' => 'decimal:2',
            'costo_repuestos' => 'integer',
            'costo_mano_obra' => 'integer',
            'costo_total' => 'integer',
        ];
    }

    public function vehiculo(): BelongsTo
    {
        return $this->belongsTo(Vehiculo::class, 'vehiculo_id');
    }

    public function tecnicoResponsable(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tecnico_responsable_id');
    }

    public function conductorAsignado(): BelongsTo
    {
        return $this->belongsTo(Conductor::class, 'conductor_asignado_id');
    }

    public function repuestos(): HasMany
    {
        return $this->hasMany(MantenimientoRepuesto::class, 'mantenimiento_id');
    }

    public function documentos(): MorphMany
    {
        return $this->morphMany(Documento::class, 'documentable');
    }

    public function alertas(): HasMany
    {
        return $this->hasMany(Alerta::class, 'mantenimiento_id');
    }

    public function esCorrectivo(): bool
    {
        return $this->tipo === 'correctivo';
    }

    public function estaCompletado(): bool
    {
        return $this->estado === 'completado';
    }

    public function tieneEvidenciaObligatoria(): bool
    {
        return $this->documentos()
            ->whereIn('tipo', ['evidencia_factura', 'evidencia_foto'])
            ->exists();
    }
}
