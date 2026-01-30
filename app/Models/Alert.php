<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Alert extends Model
{
    protected $fillable = [
        'vehicle_id',
        'spare_part_id',
        'maintenance_id',
        'type',
        'severity',
        'title',
        'message',
        'generated_at',
        'due_date',
        'status',
        'closed_by_id',
        'closed_at',
        'metadata',
        'snoozed_until',
        'snoozed_by_id',
        'snoozed_reason',
    ];

    protected function casts(): array
    {
        return [
            'generated_at' => 'datetime',
            'due_date' => 'date',
            'closed_at' => 'datetime',
            'snoozed_until' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }

    public function sparePart(): BelongsTo
    {
        return $this->belongsTo(SparePart::class, 'spare_part_id');
    }

    public function maintenance(): BelongsTo
    {
        return $this->belongsTo(Maintenance::class, 'maintenance_id');
    }

    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by_id');
    }

    public function snoozedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'snoozed_by_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', '!=', 'closed');
    }

    public function scopeSnoozed($query)
    {
        return $query->whereNotNull('snoozed_until')->where('snoozed_until', '>', now());
    }
}
