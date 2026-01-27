<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Certificacion extends Model
{
    use SoftDeletes;

    protected $table = 'certificaciones';

    protected $fillable = [
        'vehiculo_id',
        'tipo',
        'nombre',
        'numero_certificado',
        'fecha_emision',
        'fecha_vencimiento',
        'proveedor',
        'costo',
        'archivo_adjunto',
        'archivo_adjunto_2',
        'observaciones',
        'obligatorio',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'fecha_emision' => 'date',
            'fecha_vencimiento' => 'date',
            'obligatorio' => 'boolean',
            'activo' => 'boolean',
            'costo' => 'integer',
        ];
    }

    public function vehiculo(): BelongsTo
    {
        return $this->belongsTo(Vehiculo::class, 'vehiculo_id');
    }

    public function alertas(): HasMany
    {
        return $this->hasMany(Alerta::class, 'certificacion_id');
    }

    public function estaVencida(): bool
    {
        return $this->fecha_vencimiento->isPast();
    }

    public function estaPorVencer(int $dias = 30): bool
    {
        return $this->fecha_vencimiento->isFuture()
            && $this->fecha_vencimiento->lte(now()->addDays($dias));
    }
}
