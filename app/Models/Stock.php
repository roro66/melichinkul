<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Stock extends Model
{
    protected $table = 'stock';

    protected $fillable = [
        'spare_part_id',
        'quantity',
        'min_stock',
        'location',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'min_stock' => 'integer',
        ];
    }

    public function sparePart(): BelongsTo
    {
        return $this->belongsTo(SparePart::class);
    }

    public function isBelowMinimum(): bool
    {
        if ($this->min_stock === null) {
            return false;
        }

        return $this->quantity < $this->min_stock;
    }
}
