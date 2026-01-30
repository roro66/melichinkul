<?php

namespace App\Http\Controllers;

use App\Models\InventoryMovement;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class InventoryMovementController extends Controller
{
    public function index()
    {
        if (request()->ajax() || request()->has('draw')) {
            $movements = InventoryMovement::with(['sparePart', 'user'])
                ->select('inventory_movements.*')
                ->orderByDesc('movement_date')
                ->orderByDesc('id');

            $sparePartId = request()->input('spare_part_id');
            if ($sparePartId !== null && $sparePartId !== '') {
                $movements->where('spare_part_id', $sparePartId);
            }

            $typeFilter = request()->input('columns.2.search.value');
            if ($typeFilter !== null && $typeFilter !== '') {
                $movements->where('type', $typeFilter);
            }

            if (request()->has('search') && request()->get('search')['value']) {
                $search = request()->get('search')['value'];
                $movements->where(function ($query) use ($search) {
                    $query->whereHas('sparePart', fn ($q) => $q->where('code', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%"))
                        ->orWhere('notes', 'like', "%{$search}%");
                });
            }

            return DataTables::of($movements)
                ->addColumn('spare_part_code', function (InventoryMovement $m) {
                    $p = $m->sparePart;
                    if (! $p) {
                        return '—';
                    }
                    return '<a href="' . route('repuestos.show', $p->id) . '" class="text-indigo-600 dark:text-indigo-400 hover:underline">' . e($p->code) . '</a>';
                })
                ->addColumn('spare_part_description', function (InventoryMovement $m) {
                    return $m->sparePart?->description ?? '—';
                })
                ->addColumn('type_label', function (InventoryMovement $m) {
                    return InventoryMovement::TYPES[$m->type] ?? $m->type;
                })
                ->addColumn('quantity_display', function (InventoryMovement $m) {
                    $q = $m->quantity;
                    $class = $q > 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400';
                    return "<span class='{$class} font-medium'>" . ($q > 0 ? '+' : '') . $q . '</span>';
                })
                ->addColumn('movement_date_formatted', function (InventoryMovement $m) {
                    return $m->movement_date?->format('d/m/Y') ?? '—';
                })
                ->addColumn('user_name', function (InventoryMovement $m) {
                    return $m->user?->name ?? '—';
                })
                ->addColumn('notes_short', function (InventoryMovement $m) {
                    $n = $m->notes;
                    return $n ? (strlen($n) > 40 ? substr($n, 0, 40) . '…' : $n) : '—';
                })
                ->rawColumns(['spare_part_code', 'quantity_display'])
                ->make(true);
        }

        $sparePartId = request()->query('spare_part_id');

        return view('inventario.movimientos.index', ['sparePartId' => $sparePartId]);
    }
}
