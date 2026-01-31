<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MaintenanceChecklistItem extends Model
{
    protected $table = 'maintenance_checklist_items';

    protected $fillable = [
        'name',
        'description',
        'type',
        'is_required',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
        ];
    }

    public function completions(): HasMany
    {
        return $this->hasMany(MaintenanceChecklistCompletion::class, 'maintenance_checklist_item_id');
    }

    /**
     * Whether this item applies to the given maintenance type (null = all types).
     */
    public function appliesToType(?string $maintenanceType): bool
    {
        if ($this->type === null || $this->type === '') {
            return true;
        }
        return $this->type === $maintenanceType;
    }
}
