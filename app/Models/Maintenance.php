<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
        'approved_by_id',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_date' => 'date',
            'start_date' => 'date',
            'end_date' => 'date',
            'approved_at' => 'datetime',
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

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_id');
    }

    public function spareParts(): BelongsToMany
    {
        return $this->belongsToMany(SparePart::class, 'maintenance_spare_parts')
            ->withPivot('quantity')
            ->withTimestamps();
    }

    public function maintenanceSpareParts(): HasMany
    {
        return $this->hasMany(MaintenanceSparePart::class);
    }

    public function checklistCompletions(): HasMany
    {
        return $this->hasMany(MaintenanceChecklistCompletion::class);
    }

    /**
     * Checklist items that apply to this maintenance's type, ordered by sort_order.
     */
    public function getChecklistItemsForMaintenance(): \Illuminate\Database\Eloquent\Collection
    {
        return MaintenanceChecklistItem::where(function ($q) {
            $q->whereNull('type')->orWhere('type', $this->type);
        })->orderBy('sort_order')->orderBy('id')->get();
    }

    /**
     * Whether all required checklist items (for this maintenance type) are completed.
     */
    public function hasRequiredChecklistCompleted(): bool
    {
        $items = $this->getChecklistItemsForMaintenance();
        $required = $items->where('is_required', true);
        if ($required->isEmpty()) {
            return true;
        }
        $completedIds = $this->checklistCompletions()->pluck('maintenance_checklist_item_id')->all();
        foreach ($required as $item) {
            if (! in_array($item->id, $completedIds, true)) {
                return false;
            }
        }
        return true;
    }

    public function isCorrective(): bool
    {
        return $this->type === 'corrective';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isPendingApproval(): bool
    {
        return $this->status === 'pending_approval';
    }

    public function requiresApprovalByCost(): bool
    {
        $threshold = (int) config('maintenance.approval_threshold', 500_000);

        return $this->total_cost > $threshold;
    }

    /**
     * Whether the maintenance has at least one evidence file (invoice PDF or photo).
     * Required for closing corrective maintenances.
     */
    public function hasRequiredEvidence(): bool
    {
        return ! empty($this->evidence_invoice_path) || ! empty($this->evidence_photo_path);
    }

    /**
     * Process spare parts usage: create inventory movements (type use) and decrease stock.
     * Only processes pivot rows that do not yet have an InventoryMovement (idempotent).
     */
    public function processSparePartsUsage(): void
    {
        $pivots = $this->maintenanceSpareParts()->with('sparePart')->get();
        if ($pivots->isEmpty()) {
            return;
        }

        DB::transaction(function () use ($pivots) {
            foreach ($pivots as $pivot) {
                $existing = InventoryMovement::where('reference_type', MaintenanceSparePart::class)
                    ->where('reference_id', $pivot->id)
                    ->exists();
                if ($existing) {
                    continue;
                }

                $qty = - (int) $pivot->quantity;
                $sparePartId = $pivot->spare_part_id;

                $stock = Stock::firstOrCreate(
                    ['spare_part_id' => $sparePartId],
                    ['quantity' => 0, 'min_stock' => null, 'location' => null]
                );
                $stock->increment('quantity', $qty);

                InventoryMovement::create([
                    'spare_part_id' => $sparePartId,
                    'type' => InventoryMovement::TYPE_USE,
                    'quantity' => $qty,
                    'reference_type' => MaintenanceSparePart::class,
                    'reference_id' => $pivot->id,
                    'user_id' => auth()->id(),
                    'notes' => 'Uso en mantenimiento #' . $this->id,
                    'movement_date' => $this->end_date ?? now()->toDateString(),
                ]);
            }
        });
    }
}
