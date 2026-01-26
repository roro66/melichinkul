<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MantenimientoRepuesto extends Model
{
    protected $table = 'mantenimiento_repuestos';

    protected $fillable = [
        'mantenimiento_id',
        'repuesto_id',
        'codigo_repuesto',
        'descripcion',
        'cantidad',
        'precio_unitario',
        'subtotal',
    ];

    protected function casts(): array
    {
        return [
            'cantidad' => 'decimal:2',
            'precio_unitario' => 'integer',
            'subtotal' => 'integer',
        ];
    }

    public function mantenimiento(): BelongsTo
    {
        return $this->belongsTo(Mantenimiento::class, 'mantenimiento_id');
    }

    public function repuesto(): BelongsTo
    {
        return $this->belongsTo(Repuesto::class, 'repuesto_id');
    }
}
