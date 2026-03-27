<?php

use App\Models\Maintenance;
use App\Models\MaintenancePurchaseItem;
use App\Models\MaintenanceTemplate;
use App\Models\InventoryMovement;
use App\Models\SparePart;
use App\Models\Stock;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Driver;
use App\Notifications\MaintenanceAssignedToTechnicianNotification;
use App\Notifications\MaintenanceCreatedSupervisorNotification;
use App\Notifications\MaintenancePendingApprovalNotification;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

new class extends Component
{
    use WithFileUploads;

    public $maintenanceId = null;
    public $template_id = "";
    public $vehicle_id = "";
    public $type = "preventive";
    public $status = "scheduled";
    public $scheduled_date = "";
    public $start_date = "";
    public $end_date = "";
    public $mileage_at_maintenance = "";
    public $hours_at_maintenance = "";
    public $entry_reason = "";
    public $work_description = "";
    public $work_performed = "";
    public $parts_cost = 0;
    public $labor_cost = 0;
    public $total_cost = 0;
    public $hours_worked = "";
    public $workshop_supplier = "";
    public $responsible_technician_id = "";
    public $assigned_driver_id = "";
    public $observations = "";
    public $evidence_invoice = null;
    public $evidence_photo = null;
    public array $purchaseItems = [];

    protected function rules(): array
    {
        $technicianIds = User::role("technician")
            ->where("active", true)
            ->pluck("id")
            ->all();
        $technicianRule = count($technicianIds) > 0
            ? [Rule::in($technicianIds)]
            : [Rule::in([-1])];

        return [
            "vehicle_id" => ["required", "exists:vehicles,id"],
            "type" => ["required", "string", "in:preventive,corrective,inspection"],
            "status" => ["required", "string", "in:scheduled,in_progress,completed,pending_approval,cancelled"],
            "scheduled_date" => ["required", "date"],
            "start_date" => ["nullable", "date"],
            "end_date" => ["nullable", "date"],
            "mileage_at_maintenance" => ["nullable", "numeric", "min:0"],
            "hours_at_maintenance" => ["nullable", "numeric", "min:0"],
            "entry_reason" => ["nullable", "string"],
            "work_description" => ["required", "string"],
            "work_performed" => ["nullable", "string"],
            "parts_cost" => ["nullable", "integer", "min:0"],
            "labor_cost" => ["nullable", "integer", "min:0"],
            "total_cost" => ["nullable", "integer", "min:0"],
            "hours_worked" => ["nullable", "numeric", "min:0"],
            "workshop_supplier" => ["nullable", "string", "max:255"],
            "responsible_technician_id" => array_merge(["required"], $technicianRule),
            "assigned_driver_id" => ["nullable", "exists:drivers,id"],
            "observations" => ["nullable", "string"],
            "evidence_invoice" => ["nullable", "file", "mimes:pdf,jpg,jpeg,png", "max:10240"],
            "evidence_photo" => ["nullable", "file", "mimes:pdf,jpg,jpeg,png", "max:10240"],
            "purchaseItems" => ["array"],
            "purchaseItems.*.id" => ["nullable", "integer"],
            "purchaseItems.*.spare_part_id" => ["nullable", "exists:spare_parts,id"],
            "purchaseItems.*.product_name" => ["nullable", "string", "max:255"],
            "purchaseItems.*.supplier_name" => ["nullable", "string", "max:255"],
            "purchaseItems.*.document_number" => ["nullable", "string", "max:255"],
            "purchaseItems.*.unit_price" => ["nullable", "integer", "min:0"],
            "purchaseItems.*.quantity" => ["nullable", "integer", "min:1"],
            "purchaseItems.*.document_image" => ["nullable", "file", "mimes:pdf,jpg,jpeg,png", "max:10240"],
        ];
    }

    protected $messages = [
        "vehicle_id.required" => "El vehículo es obligatorio.",
        "work_description.required" => "La descripción del trabajo es obligatoria.",
        "scheduled_date.required" => "La fecha programada es obligatoria.",
        "responsible_technician_id.required" => "Debe asignar un técnico responsable (mecánico).",
        "responsible_technician_id.in" => "Seleccione un técnico mecánico activo.",
        "evidence_invoice.max" => "El documento no debe superar 10 MB.",
        "evidence_photo.max" => "La foto no debe superar 10 MB.",
    ];

    public function mount($id = null, $vehicleId = null)
    {
        if ($id) {
            $maintenance = Maintenance::findOrFail($id);
            $this->maintenanceId = $maintenance->id;
            $this->vehicle_id = $maintenance->vehicle_id;
            $this->type = $maintenance->type;
            $this->status = $maintenance->status;
            $this->scheduled_date = $maintenance->scheduled_date?->format("Y-m-d");
            $this->start_date = $maintenance->start_date?->format("Y-m-d");
            $this->end_date = $maintenance->end_date?->format("Y-m-d");
            $this->mileage_at_maintenance = $maintenance->mileage_at_maintenance;
            $this->hours_at_maintenance = $maintenance->hours_at_maintenance;
            $this->entry_reason = $maintenance->entry_reason;
            $this->work_description = $maintenance->work_description;
            $this->work_performed = $maintenance->work_performed;
            $this->parts_cost = $maintenance->parts_cost;
            $this->labor_cost = $maintenance->labor_cost;
            $this->total_cost = $maintenance->total_cost;
            $this->hours_worked = $maintenance->hours_worked;
            $this->workshop_supplier = $maintenance->workshop_supplier;
            $this->responsible_technician_id = $maintenance->responsible_technician_id;
            $this->assigned_driver_id = $maintenance->assigned_driver_id;
            $this->observations = $maintenance->observations;
            if ($this->hasPurchaseItemsTable()) {
                $this->purchaseItems = $maintenance->purchaseItems()
                    ->orderBy("id")
                    ->get()
                    ->map(fn ($item) => [
                        "id" => $item->id,
                        "spare_part_id" => $item->spare_part_id,
                        "product_name" => $item->product_name,
                        "supplier_name" => $item->supplier_name,
                        "document_number" => $item->document_number,
                        "unit_price" => (int) $item->unit_price,
                        "quantity" => (int) $item->quantity,
                        "line_total" => (int) $item->line_total,
                        "document_image_path" => $item->document_image_path,
                        "document_image" => null,
                    ])->toArray();
            }
        } elseif ($vehicleId) {
            $this->vehicle_id = $vehicleId;
        }
        if (empty($this->purchaseItems)) {
            $this->addPurchaseItemRow();
        } else {
            $this->recalculatePartsCostFromItems();
        }
    }

    public function updatedTemplateId()
    {
        if (! $this->template_id) {
            return;
        }
        $template = MaintenanceTemplate::find($this->template_id);
        if ($template) {
            if ($template->type) {
                $this->type = $template->type;
            }
            if ($template->description) {
                $this->work_description = $template->description;
            }
        }
    }

    public function updatedPartsCost()
    {
        $this->calculateTotalCost();
    }

    public function updatedLaborCost()
    {
        $this->calculateTotalCost();
    }

    public function updatedPurchaseItems($value, string $name): void
    {
        if (! preg_match('/^(\d+)\./', $name, $matches)) {
            return;
        }
        $index = (int) $matches[1];
        if (! isset($this->purchaseItems[$index])) {
            return;
        }

        if (str_ends_with($name, '.spare_part_id')) {
            $sparePartId = $this->purchaseItems[$index]["spare_part_id"] ?? null;
            if ($sparePartId) {
                $sparePart = SparePart::find($sparePartId);
                if ($sparePart) {
                    $this->purchaseItems[$index]["product_name"] = $sparePart->description ?: $sparePart->code;
                    if (empty($this->purchaseItems[$index]["unit_price"])) {
                        $this->purchaseItems[$index]["unit_price"] = (int) ($sparePart->reference_price ?? 0);
                    }
                }
            }
        }

        if (str_ends_with($name, '.unit_price') || str_ends_with($name, '.quantity') || str_ends_with($name, '.spare_part_id')) {
            $this->recalculateItemLineTotal($index);
            $this->recalculatePartsCostFromItems();
        }
    }

    public function calculateTotalCost()
    {
        $this->total_cost = ($this->parts_cost ?? 0) + ($this->labor_cost ?? 0);
    }

    public function addPurchaseItemRow(): void
    {
        $this->purchaseItems[] = [
            "id" => null,
            "spare_part_id" => null,
            "product_name" => "",
            "supplier_name" => "",
            "document_number" => "",
            "unit_price" => 0,
            "quantity" => 1,
            "line_total" => 0,
            "document_image_path" => null,
            "document_image" => null,
        ];
    }

    public function removePurchaseItemRow(int $index): void
    {
        if (! isset($this->purchaseItems[$index])) {
            return;
        }
        unset($this->purchaseItems[$index]);
        $this->purchaseItems = array_values($this->purchaseItems);
        if (empty($this->purchaseItems)) {
            $this->addPurchaseItemRow();
        }
        $this->recalculatePartsCostFromItems();
    }

    private function recalculateItemLineTotal(int $index): void
    {
        $unitPrice = (int) ($this->purchaseItems[$index]["unit_price"] ?? 0);
        $quantity = max(1, (int) ($this->purchaseItems[$index]["quantity"] ?? 1));
        $this->purchaseItems[$index]["quantity"] = $quantity;
        $this->purchaseItems[$index]["line_total"] = $unitPrice * $quantity;
    }

    private function recalculatePartsCostFromItems(): void
    {
        $partsTotal = 0;
        foreach ($this->purchaseItems as $idx => $item) {
            $this->recalculateItemLineTotal($idx);
            $partsTotal += (int) ($this->purchaseItems[$idx]["line_total"] ?? 0);
        }
        $this->parts_cost = $partsTotal;
        $this->calculateTotalCost();
    }

    public function save()
    {
        if ($this->maintenanceId && ! auth()->user()->can('maintenances.edit')) {
            abort(403, 'No tienes permiso para editar el mantenimiento completo.');
        }

        $this->validate();
        $this->validatePurchaseItemsBusinessRules();
        $this->recalculatePartsCostFromItems();

        $status = $this->status;
        $totalCost = (int) ($this->total_cost ?? 0);
        $threshold = (int) config('maintenance.approval_threshold', 500_000);
        if ($status === 'completed' && $totalCost > $threshold) {
            $status = 'pending_approval';
        }

        if ($status === 'completed' || $status === 'pending_approval') {
            $maintenanceForCheck = $this->maintenanceId ? Maintenance::find($this->maintenanceId) : null;
            if ($maintenanceForCheck && ! $maintenanceForCheck->hasRequiredChecklistCompleted()) {
                $this->addError('work_description', 'Debe marcar todos los ítems obligatorios del checklist antes de completar el mantenimiento.');
                return;
            }
        }

        $data = [
            "vehicle_id" => $this->vehicle_id,
            "type" => $this->type,
            "status" => $status,
            "scheduled_date" => $this->scheduled_date,
            "start_date" => $this->start_date ?: null,
            "end_date" => $this->end_date ?: null,
            "mileage_at_maintenance" => $this->mileage_at_maintenance ?: null,
            "hours_at_maintenance" => $this->hours_at_maintenance ?: null,
            "entry_reason" => $this->entry_reason ?: null,
            "work_description" => $this->work_description,
            "work_performed" => $this->work_performed ?: null,
            "parts_cost" => $this->parts_cost ?: 0,
            "labor_cost" => $this->labor_cost ?: 0,
            "total_cost" => $this->total_cost ?: 0,
            "hours_worked" => $this->hours_worked ?: null,
            "workshop_supplier" => $this->workshop_supplier ?: null,
            "responsible_technician_id" => $this->responsible_technician_id,
            "assigned_driver_id" => $this->assigned_driver_id ?: null,
            "observations" => $this->observations ?: null,
        ];

        if ($this->maintenanceId) {
            $maintenance = Maintenance::findOrFail($this->maintenanceId);
            $maintenance->update($data);
            if ($status === 'completed') {
                $maintenance->processSparePartsUsage();
            }
            if ($status === 'pending_approval') {
                $this->notifySupervisorsPendingApproval($maintenance);
            }
            session()->flash("success", $status === 'pending_approval'
                ? "Mantenimiento actualizado. El costo supera el umbral de aprobación; quedó pendiente de aprobación."
                : "Mantenimiento actualizado correctamente.");
        } else {
            $maintenance = Maintenance::create($data);
            if ($this->template_id) {
                $template = MaintenanceTemplate::with('spareParts')->find($this->template_id);
                if ($template) {
                    $template->applySparePartsTo($maintenance);
                }
            }
            if ($status === 'completed') {
                $maintenance->processSparePartsUsage();
            }
            if ($status === 'pending_approval') {
                $this->notifySupervisorsPendingApproval($maintenance);
            }
            $maintenance->load('responsibleTechnician');
            $this->notifyOnMaintenanceCreated($maintenance);
            session()->flash("success", $status === 'pending_approval'
                ? "Mantenimiento creado. El costo supera el umbral de aprobación; quedó pendiente de aprobación."
                : "Mantenimiento creado correctamente.");
        }

        $evidenceData = [];
        if ($this->evidence_invoice) {
            if ($maintenance->evidence_invoice_path && Storage::disk("public")->exists($maintenance->evidence_invoice_path)) {
                Storage::disk("public")->delete($maintenance->evidence_invoice_path);
            }
            $evidenceData["evidence_invoice_path"] = $this->evidence_invoice->store("maintenances/" . $maintenance->id, "public");
        }
        if ($this->evidence_photo) {
            if ($maintenance->evidence_photo_path && Storage::disk("public")->exists($maintenance->evidence_photo_path)) {
                Storage::disk("public")->delete($maintenance->evidence_photo_path);
            }
            $evidenceData["evidence_photo_path"] = $this->evidence_photo->store("maintenances/" . $maintenance->id, "public");
        }
        if (! empty($evidenceData)) {
            $maintenance->update($evidenceData);
        }

        if ($this->hasPurchaseItemsTable()) {
            $this->syncPurchaseItems($maintenance);
            $maintenance->refresh();
            $partsCost = (int) $maintenance->purchaseItems()->sum("line_total");
            $maintenance->update([
                "parts_cost" => $partsCost,
                "total_cost" => $partsCost + (int) ($maintenance->labor_cost ?? 0),
            ]);
        }

        if (! $this->maintenanceId && $maintenance->id) {
            return redirect()->route("mantenimientos.show", $maintenance->id);
        }
        return redirect()->route("mantenimientos.index");
    }

    private function notifySupervisorsPendingApproval(Maintenance $maintenance): void
    {
        $recipients = User::role(['administrator', 'supervisor'])
            ->where('active', true)
            ->get();
        foreach ($recipients as $user) {
            $user->notify(new MaintenancePendingApprovalNotification($maintenance));
        }
    }

    /** Técnico asignado + supervisores al crear mantenimiento. */
    private function notifyOnMaintenanceCreated(Maintenance $maintenance): void
    {
        $tech = User::find($maintenance->responsible_technician_id);
        if ($tech && ($tech->active ?? true)) {
            $tech->notify(new MaintenanceAssignedToTechnicianNotification($maintenance));
        }

        $supervisors = User::role('supervisor')->where('active', true)->get();
        foreach ($supervisors as $user) {
            $user->notify(new MaintenanceCreatedSupervisorNotification($maintenance));
        }
    }

    public function render()
    {
        $vehicles = Vehicle::where("status", "!=", "decommissioned")->orderBy("license_plate")->get();
        $technicians = User::role('technician')->where('active', true)->orderBy('name')->get();
        $drivers = Driver::where("active", true)->orderBy("full_name")->get();
        $templates = MaintenanceTemplate::orderBy('name')->get();
        $maintenance = $this->maintenanceId ? Maintenance::find($this->maintenanceId) : null;
        $spareParts = SparePart::where("active", true)->orderBy("code")->get();

        return view("livewire.mantenimientos.maintenance-form", [
            "vehicles" => $vehicles,
            "technicians" => $technicians,
            "drivers" => $drivers,
            "templates" => $templates,
            "maintenance" => $maintenance,
            "spareParts" => $spareParts,
        ]);
    }

    private function validatePurchaseItemsBusinessRules(): void
    {
        foreach ($this->purchaseItems as $idx => $item) {
            $sparePartId = $item["spare_part_id"] ?? null;
            $productName = trim((string) ($item["product_name"] ?? ""));
            $supplierName = trim((string) ($item["supplier_name"] ?? ""));
            $documentNumber = trim((string) ($item["document_number"] ?? ""));
            $unitPrice = (int) ($item["unit_price"] ?? 0);
            $quantity = (int) ($item["quantity"] ?? 0);
            $hasImage = ! empty($item["document_image"]) || ! empty($item["document_image_path"]);
            $hasAnyData = $sparePartId || $productName !== "" || $supplierName !== "" || $documentNumber !== "" || $unitPrice > 0 || $quantity > 0 || $hasImage;

            if (! $hasAnyData) {
                continue;
            }
            if (! $sparePartId && $productName === "") {
                $this->addError("purchaseItems.$idx.product_name", "Debe seleccionar repuesto de bodega o indicar un producto manual.");
            }
            if ($supplierName === "") {
                $this->addError("purchaseItems.$idx.supplier_name", "Proveedor obligatorio.");
            }
            if ($documentNumber === "") {
                $this->addError("purchaseItems.$idx.document_number", "Número de documento obligatorio.");
            }
            if ($quantity < 1) {
                $this->addError("purchaseItems.$idx.quantity", "La cantidad debe ser al menos 1.");
            }
        }

        if (! empty($this->getErrorBag()->toArray())) {
            throw \Illuminate\Validation\ValidationException::withMessages($this->getErrorBag()->toArray());
        }
    }

    private function syncPurchaseItems(Maintenance $maintenance): void
    {
        DB::transaction(function () use ($maintenance) {
            $lines = collect($this->purchaseItems)
                ->filter(function ($item) {
                    $sparePartId = $item["spare_part_id"] ?? null;
                    $productName = trim((string) ($item["product_name"] ?? ""));
                    $supplierName = trim((string) ($item["supplier_name"] ?? ""));
                    $documentNumber = trim((string) ($item["document_number"] ?? ""));
                    $unitPrice = (int) ($item["unit_price"] ?? 0);
                    $quantity = (int) ($item["quantity"] ?? 0);
                    $hasImage = ! empty($item["document_image"]) || ! empty($item["document_image_path"]);
                    return $sparePartId || $productName !== "" || $supplierName !== "" || $documentNumber !== "" || $unitPrice > 0 || $quantity > 0 || $hasImage;
                })
                ->values()
                ->all();

            $existingItems = $maintenance->purchaseItems()->get()->keyBy("id");
            $incomingIds = collect($lines)->pluck("id")->filter()->map(fn ($id) => (int) $id)->all();
            $deleteIds = $existingItems->keys()->diff($incomingIds);

            foreach ($deleteIds as $deleteId) {
                $toDelete = $existingItems->get($deleteId);
                if ($toDelete && $toDelete->document_image_path && Storage::disk("public")->exists($toDelete->document_image_path)) {
                    Storage::disk("public")->delete($toDelete->document_image_path);
                }
            }

            $this->rollbackPurchaseStockMovements($maintenance);
            $maintenance->purchaseItems()->whereIn("id", $deleteIds)->delete();

            $this->purchaseItems = $lines;
            foreach ($this->purchaseItems as $idx => $line) {
                $lineTotal = (int) ($line["line_total"] ?? 0);
                $lineData = [
                    "spare_part_id" => $line["spare_part_id"] ?: null,
                    "product_name" => trim((string) ($line["product_name"] ?? "")),
                    "supplier_name" => trim((string) ($line["supplier_name"] ?? "")),
                    "document_number" => trim((string) ($line["document_number"] ?? "")),
                    "unit_price" => (int) ($line["unit_price"] ?? 0),
                    "quantity" => max(1, (int) ($line["quantity"] ?? 1)),
                    "line_total" => $lineTotal,
                ];

                $purchaseItem = null;
                $itemId = (int) ($line["id"] ?? 0);
                if ($itemId && $existingItems->has($itemId)) {
                    $purchaseItem = $existingItems->get($itemId);
                    $purchaseItem->update($lineData);
                } else {
                    $purchaseItem = $maintenance->purchaseItems()->create($lineData);
                }

                if (! empty($line["document_image"])) {
                    if ($purchaseItem->document_image_path && Storage::disk("public")->exists($purchaseItem->document_image_path)) {
                        Storage::disk("public")->delete($purchaseItem->document_image_path);
                    }
                    $purchaseItem->update([
                        "document_image_path" => $line["document_image"]->store("maintenances/" . $maintenance->id . "/purchase-items", "public"),
                    ]);
                }

                $this->purchaseItems[$idx]["id"] = $purchaseItem->id;
                $this->purchaseItems[$idx]["document_image_path"] = $purchaseItem->document_image_path;
            }

            $this->applyPurchaseStockMovements($maintenance);
        });
    }

    private function rollbackPurchaseStockMovements(Maintenance $maintenance): void
    {
        $purchaseItemIds = $maintenance->purchaseItems()->pluck("id")->all();
        if (empty($purchaseItemIds)) {
            return;
        }

        $movements = InventoryMovement::where("reference_type", MaintenancePurchaseItem::class)
            ->whereIn("reference_id", $purchaseItemIds)
            ->get();

        foreach ($movements as $movement) {
            $stock = Stock::firstOrCreate(
                ["spare_part_id" => $movement->spare_part_id],
                ["quantity" => 0, "min_stock" => null, "location" => null]
            );
            $stock->increment("quantity", -1 * (int) $movement->quantity);
            $movement->delete();
        }
    }

    private function applyPurchaseStockMovements(Maintenance $maintenance): void
    {
        $itemsWithStock = $maintenance->purchaseItems()
            ->whereNotNull("spare_part_id")
            ->where("quantity", ">", 0)
            ->get();

        foreach ($itemsWithStock as $item) {
            $qty = -1 * (int) $item->quantity;
            $stock = Stock::firstOrCreate(
                ["spare_part_id" => $item->spare_part_id],
                ["quantity" => 0, "min_stock" => null, "location" => null]
            );
            $stock->increment("quantity", $qty);

            InventoryMovement::create([
                "spare_part_id" => $item->spare_part_id,
                "type" => InventoryMovement::TYPE_USE,
                "quantity" => $qty,
                "reference_type" => MaintenancePurchaseItem::class,
                "reference_id" => $item->id,
                "user_id" => auth()->id(),
                "notes" => "Uso registrado en edición de mantenimiento #" . $maintenance->id,
                "movement_date" => now()->toDateString(),
            ]);
        }
    }

    private function hasPurchaseItemsTable(): bool
    {
        try {
            return Schema::hasTable("maintenance_purchase_items");
        } catch (\Throwable) {
            return false;
        }
    }
};
?>
