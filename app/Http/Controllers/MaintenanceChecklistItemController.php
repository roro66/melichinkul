<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceChecklistItem;
use Illuminate\Http\Request;

class MaintenanceChecklistItemController extends Controller
{
    public function index()
    {
        $items = MaintenanceChecklistItem::orderBy('sort_order')->orderBy('id')->get();
        return view('checklist.index', compact('items'));
    }

    public function create()
    {
        return view('checklist.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'nullable|string|in:preventive,corrective,inspection',
            'is_required' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);
        $validated['is_required'] = $request->boolean('is_required');
        $validated['sort_order'] = (int) ($validated['sort_order'] ?? 0);

        MaintenanceChecklistItem::create($validated);
        return redirect()->route('checklist.index')->with('success', 'Ítem de checklist creado.');
    }

    public function edit(int $id)
    {
        $item = MaintenanceChecklistItem::findOrFail($id);
        return view('checklist.edit', compact('item'));
    }

    public function update(Request $request, int $id)
    {
        $item = MaintenanceChecklistItem::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'nullable|string|in:preventive,corrective,inspection',
            'is_required' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);
        $validated['is_required'] = $request->boolean('is_required');
        $validated['sort_order'] = (int) ($validated['sort_order'] ?? 0);

        $item->update($validated);
        return redirect()->route('checklist.index')->with('success', 'Ítem actualizado.');
    }

    public function destroy(int $id)
    {
        $item = MaintenanceChecklistItem::findOrFail($id);
        $item->delete();
        return redirect()->route('checklist.index')->with('success', 'Ítem eliminado.');
    }
}
