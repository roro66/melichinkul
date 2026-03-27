<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenancePurchaseItem extends Model
{
    protected $fillable = [
        'maintenance_id',
        'spare_part_id',
        'product_name',
        'supplier_name',
        'document_number',
        'unit_price',
        'quantity',
        'line_total',
        'document_image_path',
    ];

    protected function casts(): array
    {
        return [
            'spare_part_id' => 'integer',
            'unit_price' => 'integer',
            'quantity' => 'integer',
            'line_total' => 'integer',
        ];
    }

    public function maintenance(): BelongsTo
    {
        return $this->belongsTo(Maintenance::class);
    }

    public function sparePart(): BelongsTo
    {
        return $this->belongsTo(SparePart::class);
    }
}
