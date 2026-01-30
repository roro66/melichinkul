<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class MaintenanceTemplate extends Model
{
    protected $table = 'maintenance_templates';

    protected $fillable = [
        'name',
        'description',
        'type',
    ];

    public function spareParts(): BelongsToMany
    {
        return $this->belongsToMany(SparePart::class, 'maintenance_template_spare_parts')
            ->withPivot('quantity')
            ->withTimestamps();
    }

    /**
     * Copy this template's spare parts (with quantities) to a maintenance.
     */
    public function applySparePartsTo(Maintenance $maintenance): void
    {
        $sync = $this->spareParts->mapWithKeys(function ($sp) {
            return [$sp->id => ['quantity' => $sp->pivot->quantity]];
        })->all();
        $maintenance->spareParts()->sync($sync);
    }
}
