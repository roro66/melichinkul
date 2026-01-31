<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenanceChecklistCompletion extends Model
{
    protected $table = 'maintenance_checklist_completions';

    protected $fillable = [
        'maintenance_id',
        'maintenance_checklist_item_id',
        'completed_at',
        'completed_by_id',
    ];

    protected function casts(): array
    {
        return [
            'completed_at' => 'datetime',
        ];
    }

    public function maintenance(): BelongsTo
    {
        return $this->belongsTo(Maintenance::class);
    }

    public function checklistItem(): BelongsTo
    {
        return $this->belongsTo(MaintenanceChecklistItem::class, 'maintenance_checklist_item_id');
    }

    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by_id');
    }
}
