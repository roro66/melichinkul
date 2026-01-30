<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryMovement extends Model
{
    protected $fillable = [
        'spare_part_id',
        'type',
        'quantity',
        'reference_type',
        'reference_id',
        'user_id',
        'notes',
        'movement_date',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'movement_date' => 'date',
        ];
    }

    public const TYPE_PURCHASE = 'purchase';
    public const TYPE_ADJUSTMENT_IN = 'adjustment_in';
    public const TYPE_ADJUSTMENT_OUT = 'adjustment_out';
    public const TYPE_USE = 'use';
    public const TYPE_RETURN = 'return';

    public const TYPES = [
        self::TYPE_PURCHASE => 'Compra',
        self::TYPE_ADJUSTMENT_IN => 'Ajuste entrada',
        self::TYPE_ADJUSTMENT_OUT => 'Ajuste salida',
        self::TYPE_USE => 'Uso',
        self::TYPE_RETURN => 'Devolución',
    ];

    public function sparePart(): BelongsTo
    {
        return $this->belongsTo(SparePart::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isIn(): bool
    {
        return $this->quantity > 0;
    }

    public function isOut(): bool
    {
        return $this->quantity < 0;
    }
}
