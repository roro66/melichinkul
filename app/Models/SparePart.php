<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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

    public function stock(): HasOne
    {
        return $this->hasOne(Stock::class);
    }

    public function purchaseItems(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function inventoryMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }

    public function maintenances(): BelongsToMany
    {
        return $this->belongsToMany(Maintenance::class, 'maintenance_spare_parts')
            ->withPivot('quantity')
            ->withTimestamps();
    }

    public function currentStock(): ?Stock
    {
        return $this->stock;
    }
}
