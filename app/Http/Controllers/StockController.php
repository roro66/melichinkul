<?php

namespace App\Http\Controllers;

use App\Models\InventoryMovement;
use App\Models\SparePart;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    public function showAdjustForm(int $id)
    {
        $sparePart = SparePart::with('stock')->findOrFail($id);

        return view('repuestos.ajustar', ['sparePart' => $sparePart]);
    }

    public function storeAdjustment(Request $request, int $id)
    {
        $sparePart = SparePart::with('stock')->findOrFail($id);

        $validated = $request->validate([
            'type' => 'required|in:adjustment_in,adjustment_out',
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:500',
        ]);

        $quantity = (int) $validated['quantity'];
        $isIn = $validated['type'] === 'adjustment_in';
        $signedQty = $isIn ? $quantity : -$quantity;

        if (! $isIn) {
            $current = $sparePart->stock?->quantity ?? 0;
            if ($current < $quantity) {
                return back()->withInput()->withErrors([
                    'quantity' => "Stock actual ({$current}) insuficiente para una salida de {$quantity}.",
                ]);
            }
        }

        DB::transaction(function () use ($sparePart, $validated, $signedQty, $quantity) {
            $stock = Stock::firstOrCreate(
                ['spare_part_id' => $sparePart->id],
                ['quantity' => 0, 'min_stock' => null, 'location' => null]
            );
            $stock->increment('quantity', $signedQty);

            InventoryMovement::create([
                'spare_part_id' => $sparePart->id,
                'type' => $validated['type'],
                'quantity' => $signedQty,
                'reference_type' => null,
                'reference_id' => null,
                'user_id' => auth()->id(),
                'notes' => $validated['notes'] ?? 'Ajuste manual',
                'movement_date' => now()->toDateString(),
            ]);
        });

        $action = $validated['type'] === 'adjustment_in' ? 'entrada' : 'salida';

        return redirect()->route('repuestos.show', $sparePart->id)
            ->with('success', "Ajuste de {$action} de {$quantity} unidad(es) registrado correctamente.");
    }
}
