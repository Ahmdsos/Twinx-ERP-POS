<?php

namespace Modules\HR\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\HR\Models\Employee;
use Modules\HR\Models\DeliveryDriver;
use Modules\HR\Enums\DeliveryDriverStatus;
use Illuminate\Support\Facades\DB;

class DeliveryController extends Controller
{
    /**
     * Display a listing of delivery drivers.
     */
    public function index(Request $request)
    {
        $query = DeliveryDriver::with('employee');

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('employee', function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('employee_code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $drivers = $query->paginate(15)->withQueryString();

        $stats = [
            'total' => DeliveryDriver::count(),
            'available' => DeliveryDriver::where('status', 'available')->count(),
            'on_delivery' => DeliveryDriver::where('status', 'on_delivery')->count(),
            'offline' => DeliveryDriver::where('status', 'offline')->count(),
        ];

        return view('hr::delivery.index', compact('drivers', 'stats'));
    }

    /**
     * Show the form for creating a new delivery driver.
     */
    public function create()
    {
        // Redirect to Employee creation as per unified workflow
        return redirect()->route('hr.employees.create')
            ->with('info', 'يرجى تسجيل السائق كموظف جديد وتفعيل خيار "تعيين كسائق توصيل".');
    }

    /**
     * Store a newly created delivery driver in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:hr_employees,id|unique:hr_delivery_drivers,employee_id',
            'license_number' => 'required|string|max:50',
            'vehicle_type' => 'nullable|string|max:50',
            'vehicle_plate' => 'nullable|string|max:20',
        ]);

        try {
            $data = $request->all();
            $data['status'] = DeliveryDriverStatus::AVAILABLE->value; // Default status

            DeliveryDriver::create($data);

            return redirect()->route('hr.delivery.index')->with('success', 'تم إضافة سائق جديد بنجاح');
        } catch (\Exception $e) {
            return back()->with('error', 'حدث خطأ أثناء التسجيل: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified delivery driver.
     */
    public function edit(DeliveryDriver $driver)
    {
        $driver->load('employee');
        return view('hr::delivery.edit', compact('driver'));
    }

    /**
     * Update the specified delivery driver in storage.
     */
    public function update(Request $request, DeliveryDriver $driver)
    {
        $request->validate([
            'license_number' => 'nullable|string|max:50',
            'license_expiry' => 'nullable|date',
            'vehicle_info' => 'nullable|string|max:255',
        ]);

        try {
            $driver->update($request->except('status')); // Prevent status update from here

            return redirect()->route('hr.delivery.index')
                ->with('success', 'تم تحديث بيانات السائق بنجاح.');
        } catch (\Exception $e) {
            return back()->with('error', 'حدث خطأ أثناء التحديث: ' . $e->getMessage());
        }
    }

    /**
     * Update status quickly.
     */
    public function updateStatus(Request $request, DeliveryDriver $driver)
    {
        $request->validate([
            'status' => 'required|in:available,on_delivery,offline,suspended',
        ]);

        $driver->update(['status' => $request->status]);

        return back()->with('success', 'تم تحديث حالة السائق: ' . $request->status);
    }

    /**
     * Display the specified delivery driver.
     */
    public function show(DeliveryDriver $driver)
    {
        $driver->load([
            'employee',
            'shipments' => function ($q) {
                $q->with(['customer', 'salesOrder', 'salesInvoice'])->orderBy('created_at', 'desc')->limit(50);
            }
        ]);

        $activeMissions = $driver->activeShipments()->with(['customer', 'salesOrder', 'salesInvoice'])->get();

        $stats = [
            'total_all_time' => $driver->shipments()->count(),
            'success_rate' => $driver->success_rate,
            'delivered_today' => $driver->shipments()->where('status', \Modules\Sales\Enums\DeliveryStatus::DELIVERED)->whereDate('updated_at', now())->count(),
            'returned_today' => $driver->shipments()->where('status', \Modules\Sales\Enums\DeliveryStatus::RETURNED)->whereDate('updated_at', now())->count(),
        ];

        return view('hr::delivery.show', compact('driver', 'activeMissions', 'stats'));
    }

    /**
     * Remove the specified delivery driver from storage.
     */
    public function destroy(DeliveryDriver $driver)
    {
        try {
            $driver->delete();
            return redirect()->route('hr.delivery.index')
                ->with('success', 'تم إزالة ملف السائق بنجاح.');
        } catch (\Exception $e) {
            return back()->with('error', 'حدث خطأ أثناء الحذف.');
        }
    }
}
