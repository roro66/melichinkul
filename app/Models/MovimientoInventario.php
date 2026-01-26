<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MovimientoInventario extends Model
{
    protected $table = 'movimientos_inventario';

    protected $fillable = [
        'repuesto_id',
        'tipo',
        'cantidad',
        'fecha',
        'compra_id',
        'mantenimiento_id',
        'referencia_manual',
        'observaciones',
        'usuario_id',
    ];

    protected function casts(): array
    {
        return [
            'cantidad' => 'decimal:2',
            'fecha' => 'date',
        ];
    }

    public function repuesto(): BelongsTo
    {
        return $this->belongsTo(Repuesto::class, 'repuesto_id');
    }

    public function mantenimiento(): BelongsTo
    {
        return $this->belongsTo(Mantenimiento::class, 'mantenimiento_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function esEntrada(): bool
    {
        return in_array($this->tipo, [
            'entrada_compra',
            'entrada_ajuste',
        ], true);
    }

    public function esSalida(): bool
    {
        return in_array($this->tipo, [
            'salida_mantenimiento',
            'salida_ajuste',
            'salida_vencimiento',
        ], true);
    }
}
