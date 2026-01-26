<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Alerta extends Model
{
    protected $fillable = [
        'vehiculo_id',
        'conductor_id',
        'certificacion_id',
        'mantenimiento_id',
        'repuesto_id',
        'tipo',
        'severidad',
        'titulo',
        'mensaje',
        'fecha_generada',
        'fecha_limite',
        'estado',
        'cerrada_por_id',
        'fecha_cierre',
        'motivo_cierre',
        'metadata',
        'snoozed_until',
        'snoozed_by',
        'snoozed_reason',
    ];

    protected function casts(): array
    {
        return [
            'fecha_generada' => 'datetime',
            'fecha_limite' => 'date',
            'fecha_cierre' => 'datetime',
            'snoozed_until' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function vehiculo(): BelongsTo
    {
        return $this->belongsTo(Vehiculo::class, 'vehiculo_id');
    }

    public function conductor(): BelongsTo
    {
        return $this->belongsTo(Conductor::class, 'conductor_id');
    }

    public function certificacion(): BelongsTo
    {
        return $this->belongsTo(Certificacion::class, 'certificacion_id');
    }

    public function mantenimiento(): BelongsTo
    {
        return $this->belongsTo(Mantenimiento::class, 'mantenimiento_id');
    }

    public function repuesto(): BelongsTo
    {
        return $this->belongsTo(Repuesto::class, 'repuesto_id');
    }

    public function cerradaPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cerrada_por_id');
    }

    public function snoozedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'snoozed_by');
    }

    public function estaActiva(): bool
    {
        return $this->estado !== 'cerrada';
    }

    public function estaPendiente(): bool
    {
        return $this->estado === 'pendiente';
    }

    public function estaSnoozed(): bool
    {
        return $this->snoozed_until && $this->snoozed_until->isFuture();
    }

    public function esCritica(): bool
    {
        return $this->severidad === 'critica';
    }

    /**
     * Alertas que deben mostrarse: activas y no snoozed (o snooze ya vencido).
     */
    public function scopeVigentes($query)
    {
        return $query->where('estado', '!=', 'cerrada')
            ->where(function ($q) {
                $q->whereNull('snoozed_until')
                    ->orWhere('snoozed_until', '<=', now());
            });
    }

    /**
     * Alertas actualmente snoozed (snoozed_until en el futuro).
     */
    public function scopeSnoozed($query)
    {
        return $query->whereNotNull('snoozed_until')
            ->where('snoozed_until', '>', now());
    }
}
