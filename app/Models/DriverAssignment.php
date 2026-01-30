<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DriverAssignment extends Model
{
    protected $fillable = [
        'driver_id',
        'vehicle_id',
        'assignment_date',
        'end_date',
        'assigned_by_id',
        'end_reason',
        'observations',
    ];

    protected function casts(): array
    {
        return [
            'assignment_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class, 'driver_id');
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by_id');
    }
}
