<?php

namespace App\Http\Controllers;

use App\Models\InventoryMovement;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\SparePart;
use App\Models\Stock;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class PurchaseController extends Controller
{
    public function index()
    {
        if (request()->ajax() || request()->has('draw')) {
            $purchases = Purchase::with(['supplier', 'user'])
                ->select('purchases.*');

            if (request()->has('search') && request()->get('search')['value']) {
                $search = request()->get('search')['value'];
                $purchases->where(function ($query) use ($search) {
                    $query->where('purchases.reference', 'like', "%{$search}%")
                        ->orWhereHas('supplier', fn ($q) => $q->where('name', 'like', "%{$search}%"));
                });
            }

            $statusFilter = request()->input('columns.3.search.value');
            if ($statusFilter !== null && $statusFilter !== '') {
                $purchases->where('purchases.status', $statusFilter);
            }

            return DataTables::of($purchases)
                ->addColumn('supplier_name', function (Purchase $p) {
                    return $p->supplier?->name ?? '—';
                })
                ->addColumn('purchase_date_formatted', function (Purchase $p) {
                    return $p->purchase_date?->format('d/m/Y') ?? '—';
                })
                ->addColumn('total_formatted', function (Purchase $p) {
                    return '$' . number_format($p->totalAmount(), 0, ',', '.');
                })
                ->addColumn('status_badge', function (Purchase $p) {
                    $labels = Purchase::STATUSES;
                    $label = $labels[$p->status] ?? $p->status;
                    $colors = [
                        'draft' => 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300',
                        'received' => 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300',
                        'cancelled' => 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300',
                    ];
                    $class = $colors[$p->status] ?? 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300';
                    return "<span class='inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {$class}'>{$label}</span>";
                })
                ->addColumn('actions', function (Purchase $p) {
                    $btns = "<div class='flex justify-end space-x-3'>";
                    $btns .= "<a href='" . route('compras.show', $p->id) . "' class='text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300' title='Ver'><i class='fas fa-eye'></i></a>";
                    if ($p->isDraft()) {
                        $btns .= "<a href='" . route('compras.edit', $p->id) . "' class='text-amber-600 dark:text-amber-400 hover:text-amber-900 dark:hover:text-amber-300' title='Editar'><i class='fas fa-edit'></i></a>";
                        $btns .= "<button onclick='deletePurchase(" . $p->id . ")' class='text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300 cursor-pointer bg-transparent border-none' title='Eliminar'><i class='fas fa-trash-alt'></i></button>";
                    }
                    $btns .= "</div>";
                    return $btns;
                })
                ->rawColumns(['status_badge', 'actions'])
                ->make(true);
        }

        return view('compras.index');
    }

    public function create()
    {
        $suppliers = Supplier::where('active', true)->orderBy('name')->get();
        $spareParts = SparePart::where('active', true)->orderBy('code')->get();

        return view('compras.create', [
            'suppliers' => $suppliers,
            'spareParts' => $spareParts,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'purchase_date' => 'required|date',
            'reference' => 'nullable|string|max:64',
            'notes' => 'nullable|string',
            'items' => 'nullable|array',
            'items.*.spare_part_id' => 'nullable|exists:spare_parts,id',
            'items.*.quantity' => 'nullable|integer|min:0',
            'items.*.unit_price' => 'nullable|integer|min:0',
            'items.*.expiry_date' => 'nullable|date',
        ]);

        $purchase = DB::transaction(function () use ($validated) {
            $purchase = Purchase::create([
                'supplier_id' => $validated['supplier_id'],
                'user_id' => auth()->id(),
                'purchase_date' => $validated['purchase_date'],
                'reference' => $validated['reference'] ?? null,
                'status' => Purchase::STATUS_DRAFT,
                'notes' => $validated['notes'] ?? null,
            ]);

            $items = $validated['items'] ?? [];
            foreach ($items as $row) {
                if (empty($row['spare_part_id']) || (int) ($row['quantity'] ?? 0) < 1) {
                    continue;
                }
                $purchase->items()->create([
                    'spare_part_id' => $row['spare_part_id'],
                    'quantity' => (int) $row['quantity'],
                    'unit_price' => (int) ($row['unit_price'] ?? 0),
                    'expiry_date' => ! empty($row['expiry_date']) ? $row['expiry_date'] : null,
                ]);
            }

            return $purchase;
        });

        return redirect()->route('compras.show', $purchase->id)->with('success', 'Compra creada correctamente.');
    }

    public function show(int $id)
    {
        $purchase = Purchase::with(['supplier', 'user', 'items.sparePart'])->findOrFail($id);

        return view('compras.show', ['purchase' => $purchase]);
    }

    public function edit(int $id)
    {
        $purchase = Purchase::with('items.sparePart')->findOrFail($id);
        if (! $purchase->isDraft()) {
            return redirect()->route('compras.show', $purchase->id)->with('error', 'Solo se pueden editar compras en borrador.');
        }

        $suppliers = Supplier::where('active', true)->orderBy('name')->get();
        $spareParts = SparePart::where('active', true)->orderBy('code')->get();

        return view('compras.edit', [
            'purchase' => $purchase,
            'suppliers' => $suppliers,
            'spareParts' => $spareParts,
        ]);
    }

    public function update(Request $request, int $id)
    {
        $purchase = Purchase::findOrFail($id);
        if (! $purchase->isDraft()) {
            return redirect()->route('compras.show', $purchase->id)->with('error', 'Solo se pueden editar compras en borrador.');
        }

        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'purchase_date' => 'required|date',
            'reference' => 'nullable|string|max:64',
            'notes' => 'nullable|string',
            'items' => 'nullable|array',
            'items.*.spare_part_id' => 'nullable|exists:spare_parts,id',
            'items.*.quantity' => 'nullable|integer|min:0',
            'items.*.unit_price' => 'nullable|integer|min:0',
            'items.*.expiry_date' => 'nullable|date',
        ]);

        DB::transaction(function () use ($purchase, $validated) {
            $purchase->update([
                'supplier_id' => $validated['supplier_id'],
                'purchase_date' => $validated['purchase_date'],
                'reference' => $validated['reference'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            $purchase->items()->delete();
            $items = $validated['items'] ?? [];
            foreach ($items as $row) {
                if (empty($row['spare_part_id']) || (int) ($row['quantity'] ?? 0) < 1) {
                    continue;
                }
                $purchase->items()->create([
                    'spare_part_id' => $row['spare_part_id'],
                    'quantity' => (int) $row['quantity'],
                    'unit_price' => (int) ($row['unit_price'] ?? 0),
                    'expiry_date' => ! empty($row['expiry_date']) ? $row['expiry_date'] : null,
                ]);
            }
        });

        return redirect()->route('compras.show', $purchase->id)->with('success', 'Compra actualizada correctamente.');
    }

    public function destroy(int $id)
    {
        $purchase = Purchase::findOrFail($id);
        if (! $purchase->isDraft()) {
            return response()->json(['success' => false, 'message' => 'Solo se pueden eliminar compras en borrador.'], 422);
        }
        $purchase->delete();

        return response()->json(['success' => true, 'message' => 'Compra eliminada correctamente.']);
    }

    public function receive(Request $request, int $id)
    {
        $purchase = Purchase::with('items.sparePart')->findOrFail($id);
        if (! $purchase->isDraft()) {
            return redirect()->route('compras.show', $purchase->id)->with('error', 'Solo se pueden recibir compras en borrador.');
        }
        if ($purchase->items->isEmpty()) {
            return redirect()->route('compras.show', $purchase->id)->with('error', 'La compra no tiene ítems. Agregue al menos un ítem antes de recibir.');
        }

        DB::transaction(function () use ($purchase) {
            $userId = auth()->id();
            $movementDate = $purchase->purchase_date;

            foreach ($purchase->items as $item) {
                $qty = $item->quantity;
                $sparePartId = $item->spare_part_id;

                $stock = Stock::firstOrCreate(
                    ['spare_part_id' => $sparePartId],
                    ['quantity' => 0, 'min_stock' => null, 'location' => null]
                );
                $stock->increment('quantity', $qty);

                InventoryMovement::create([
                    'spare_part_id' => $sparePartId,
                    'type' => InventoryMovement::TYPE_PURCHASE,
                    'quantity' => $qty,
                    'reference_type' => PurchaseItem::class,
                    'reference_id' => $item->id,
                    'user_id' => $userId,
                    'notes' => 'Recepción de compra #' . $purchase->id,
                    'movement_date' => $movementDate,
                ]);
            }

            $purchase->update([
                'status' => Purchase::STATUS_RECEIVED,
                'user_id' => $purchase->user_id ?? $userId,
            ]);
        });

        return redirect()->route('compras.show', $purchase->id)->with('success', 'Compra recibida. El stock se ha actualizado.');
    }
}
