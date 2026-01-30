<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SparePart extends Model
{
    protected $fillable = [
        'code',
        'description',
        'brand',
        'category',
        'reference_price',
        'has_expiration',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'reference_price' => 'integer',
            'has_expiration' => 'boolean',
            'active' => 'boolean',
        ];
    }

    public const CATEGORIES = [
        'spare_part' => 'Repuesto',
        'consumable' => 'Consumible',
        'tool' => 'Herramienta',
        'supply' => 'Insumo',
    ];
}
