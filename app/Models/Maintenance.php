<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Maintenance extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'vehicle_id',
        'type',
        'status',
        'scheduled_date',
        'start_date',
        'end_date',
        'mileage_at_maintenance',
        'hours_at_maintenance',
        'entry_reason',
        'work_description',
        'work_performed',
        'parts_cost',
        'labor_cost',
        'total_cost',
        'hours_worked',
        'workshop_supplier',
        'responsible_technician_id',
        'assigned_driver_id',
        'observations',
        'evidence_invoice_path',
        'evidence_photo_path',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_date' => 'date',
            'start_date' => 'date',
            'end_date' => 'date',
            'mileage_at_maintenance' => 'decimal:2',
            'hours_at_maintenance' => 'decimal:2',
            'hours_worked' => 'decimal:2',
            'parts_cost' => 'integer',
            'labor_cost' => 'integer',
            'total_cost' => 'integer',
        ];
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }

    public function responsibleTechnician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_technician_id');
    }

    public function assignedDriver(): BelongsTo
    {
        return $this->belongsTo(Driver::class, 'assigned_driver_id');
    }

    public function isCorrective(): bool
    {
        return $this->type === 'corrective';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Whether the maintenance has at least one evidence file (invoice PDF or photo).
     * Required for closing corrective maintenances.
     */
    public function hasRequiredEvidence(): bool
    {
        return ! empty($this->evidence_invoice_path) || ! empty($this->evidence_photo_path);
    }
}
