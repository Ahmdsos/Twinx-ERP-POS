<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Inventory\Models\Unit;

/**
 * UnitController
 * 
 * Handles web routes for unit of measurement management.
 * Uses correct column names: 'abbreviation' (not symbol)
 */
class UnitController extends Controller
{
    /**
     * Display a listing of units.
     */
    public function index()
    {
        $units = Unit::with('baseUnit')
            ->withCount('products')
            ->orderBy('name')
            ->get();

        return view('inventory.units.index', compact('units'));
    }

    /**
     * Store a newly created unit.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:units,name',
            'abbreviation' => 'required|string|max:10|unique:units,abbreviation',
            'base_unit_id' => 'nullable|exists:units,id',
            'conversion_factor' => 'nullable|numeric|min:0.0001',
            'is_base' => 'boolean',
        ]);

        // If base_unit_id is set, this is NOT a base unit
        $validated['is_base'] = empty($validated['base_unit_id']);
        $validated['is_active'] = true;

        // Default conversion factor
        if (empty($validated['conversion_factor'])) {
            $validated['conversion_factor'] = 1;
        }

        Unit::create($validated);

        return redirect()
            ->route('units.index')
            ->with('success', 'تم إضافة وحدة القياس بنجاح');
    }

    /**
     * Update the specified unit.
     */
    public function update(Request $request, Unit $unit)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:units,name,' . $unit->id,
            'abbreviation' => 'required|string|max:10|unique:units,abbreviation,' . $unit->id,
            'base_unit_id' => 'nullable|exists:units,id',
            'conversion_factor' => 'nullable|numeric|min:0.0001',
        ]);

        // Prevent self-referencing
        if (isset($validated['base_unit_id']) && $validated['base_unit_id'] == $unit->id) {
            return back()->with('error', 'لا يمكن أن تكون الوحدة أساسية لنفسها');
        }

        // If base_unit_id is set, this is NOT a base unit
        $validated['is_base'] = empty($validated['base_unit_id']);

        $unit->update($validated);

        return redirect()
            ->route('units.index')
            ->with('success', 'تم تحديث وحدة القياس بنجاح');
    }

    /**
     * Remove the specified unit.
     */
    public function destroy(Unit $unit)
    {
        // Check if unit is used by products
        if ($unit->products()->exists()) {
            return back()->with('error', 'لا يمكن حذف هذه الوحدة لأنها مستخدمة بواسطة منتجات');
        }

        $unit->delete();

        return redirect()
            ->route('units.index')
            ->with('success', 'تم حذف وحدة القياس بنجاح');
    }
}
