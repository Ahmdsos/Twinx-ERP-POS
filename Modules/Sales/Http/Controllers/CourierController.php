<?php

namespace Modules\Sales\Http\Controllers;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Modules\Sales\Models\Courier;

/**
 * CourierController - إدارة شركات الشحن
 */
class CourierController extends Controller
{
    /**
     * Display a listing of couriers
     */
    public function index(Request $request)
    {
        $query = Courier::query();

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $couriers = $query->withCount('shipments')
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        // Stats
        $stats = [
            'total' => Courier::count(),
            'active' => Courier::where('is_active', true)->count(),
            'inactive' => Courier::where('is_active', false)->count(),
        ];

        return view('sales.couriers.index', compact('couriers', 'stats'));
    }

    /**
     * Show the form for creating a new courier
     */
    public function create()
    {
        return view('sales.couriers.create');
    }

    /**
     * Store a newly created courier
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:couriers,code',
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'tracking_url_template' => 'nullable|url|max:500',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        Courier::create($validated);

        return redirect()->route('couriers.index')
            ->with('success', 'تم إضافة شركة الشحن بنجاح');
    }

    /**
     * Display the specified courier
     */
    public function show(Courier $courier)
    {
        $courier->loadCount(['shipments', 'deliveryOrders']);

        $recentShipments = $courier->shipments()
            ->with(['deliveryOrder.customer'])
            ->orderByDesc('created_at')
            ->take(10)
            ->get();

        return view('sales.couriers.show', compact('courier', 'recentShipments'));
    }

    /**
     * Show the form for editing the courier
     */
    public function edit(Courier $courier)
    {
        return view('sales.couriers.edit', compact('courier'));
    }

    /**
     * Update the specified courier
     */
    public function update(Request $request, Courier $courier)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:couriers,code,' . $courier->id,
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'tracking_url_template' => 'nullable|url|max:500',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $courier->update($validated);

        return redirect()->route('couriers.show', $courier)
            ->with('success', 'تم تحديث بيانات شركة الشحن بنجاح');
    }

    /**
     * Remove the specified courier
     */
    public function destroy(Courier $courier)
    {
        // Check if courier has shipments
        if ($courier->shipments()->exists()) {
            return back()->with('error', 'لا يمكن حذف شركة الشحن لوجود شحنات مرتبطة بها');
        }

        $courier->delete();

        return redirect()->route('couriers.index')
            ->with('success', 'تم حذف شركة الشحن بنجاح');
    }

    /**
     * Toggle courier active status
     */
    public function toggleStatus(Courier $courier)
    {
        $courier->update(['is_active' => !$courier->is_active]);

        $message = $courier->is_active
            ? 'تم تفعيل شركة الشحن بنجاح'
            : 'تم تعطيل شركة الشحن بنجاح';

        return back()->with('success', $message);
    }
}
