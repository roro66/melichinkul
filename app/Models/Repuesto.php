<?php

namespace App\Models;

use App\Services\StockCriticoService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Repuesto extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'codigo',
        'descripcion',
        'marca',
        'categoria',
        'stock_actual',
        'stock_minimo_manual',
        'precio_referencia',
        'tiene_vencimiento',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'tiene_vencimiento' => 'boolean',
            'activo' => 'boolean',
            'precio_referencia' => 'integer',
        ];
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(MovimientoInventario::class, 'repuesto_id');
    }

    public function mantenimientoRepuestos(): HasMany
    {
        return $this->hasMany(MantenimientoRepuesto::class, 'repuesto_id');
    }

    public function alertas(): HasMany
    {
        return $this->hasMany(Alerta::class, 'repuesto_id');
    }

    /**
     * Stock crítico: manual o dinámico según historial (≥90 días).
     * Usa StockCriticoService. Si no hay servicio, devuelve stock_minimo_manual.
     */
    public function getStockCriticoAttribute(): int|float
    {
        return app(StockCriticoService::class)->stockCriticoPara($this);
    }

    /**
     * True si se está usando umbral dinámico (≥90 días de movimientos).
     */
    public function getUsaStockCriticoDinamicoAttribute(): bool
    {
        return app(StockCriticoService::class)->usaDinamicoPara($this);
    }

    public function enAlertaStock(): bool
    {
        return $this->stock_actual < $this->stock_critico;
    }
}
