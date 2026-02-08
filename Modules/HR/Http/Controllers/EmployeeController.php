<?php

namespace Modules\HR\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\HR\Models\Employee;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the employees.
     */
    public function index(Request $request)
    {
        $query = Employee::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('employee_code', 'like', "%{$search}%")
                    ->orWhere('position', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('department')) {
            $query->where('department', 'like', "%{$request->department}%");
        }

        $employees = $query->with('leaves')->orderBy('id', 'desc')->paginate(10)->withQueryString();

        // Calculate stats
        $stats = [
            'total' => Employee::count(),
            'active' => Employee::where('status', \Modules\HR\Enums\EmployeeStatus::ACTIVE)->count(),
            'on_leave' => Employee::whereHas('leaves', function ($q) {
                $q->where('status', 'approved')
                    ->whereDate('start_date', '<=', now())
                    ->whereDate('end_date', '>=', now());
            })->count(),
            'total_salaries' => Employee::where('status', \Modules\HR\Enums\EmployeeStatus::ACTIVE)->sum('basic_salary'),
        ];

        return view('hr::employees.index', compact('employees', 'stats'));
    }

    /**
     * Show the form for creating a new employee.
     */
    public function create()
    {
        // Get users who are NOT already linked to an employee
        $users = User::doesntHave('employee')->get();
        return view('hr::employees.create', compact('users'));
    }

    /**
     * Store a newly created employee in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'birth_date' => 'nullable|date',
            'gender' => 'nullable|string|max:20',
            'nationality' => 'nullable|string|max:255',
            'marital_status' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'position' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'basic_salary' => 'required|numeric|min:0',
            'bank_name' => 'nullable|string|max:255',
            'bank_account_number' => 'nullable|string|max:255',
            'iban' => 'nullable|string|max:255',
            'social_security_number' => 'nullable|string|max:255',
            'contract_type' => 'nullable|string|max:50',
            'status' => 'required|in:active,inactive,on_leave,terminated',
            'user_id' => 'nullable|exists:users,id|unique:hr_employees,user_id',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
        ]);

        try {
            DB::beginTransaction();

            $employee = Employee::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'birth_date' => $request->birth_date,
                'gender' => $request->gender,
                'nationality' => $request->nationality,
                'marital_status' => $request->marital_status,
                'email' => $request->email,
                'phone' => $request->phone,
                'position' => $request->position,
                'department' => $request->department,
                'basic_salary' => $request->basic_salary,
                'bank_name' => $request->bank_name,
                'bank_account_number' => $request->bank_account_number,
                'iban' => $request->iban,
                'social_security_number' => $request->social_security_number,
                'contract_type' => $request->contract_type,
                'status' => $request->status,
                'user_id' => $request->user_id,
                'date_of_joining' => $request->date_of_joining ?? now(),
                'id_number' => $request->id_number,
                'address' => $request->address,
                'emergency_contact_name' => $request->emergency_contact_name,
                'emergency_contact_phone' => $request->emergency_contact_phone,
                'created_by' => auth()->id(),
            ]);

            // Handle Delivery Driver Creation
            if ($request->has('is_driver') && $request->is_driver == 1) {
                // Validate Driver Fields
                $request->validate([
                    'license_number' => 'required|string|max:50',
                    'license_expiry' => 'nullable|date',
                    'vehicle_type' => 'nullable|string|max:50',
                    'vehicle_plate' => 'nullable|string|max:20',
                ]);

                \Modules\HR\Models\DeliveryDriver::create([
                    'employee_id' => $employee->id,
                    'license_number' => $request->license_number,
                    'license_expiry' => $request->license_expiry,
                    'vehicle_info' => ($request->vehicle_type . ' - ' . $request->vehicle_plate),
                    'status' => \Modules\HR\Enums\DeliveryDriverStatus::AVAILABLE,
                    'rating' => 5.0, // Default rating
                    'total_deliveries' => 0,
                ]);
            }

            DB::commit();

            return redirect()->route('hr.employees.index')
                ->with('success', 'تم إضافة الموظف بنجاح.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'حدث خطأ أثناء إضافة الموظف: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified employee.
     */
    public function show(Employee $employee)
    {
        $employee->load([
            'user',
            'deliveryDriver', // Eager load driver profile
            'documents',
            'leaves',
            'attendance' => function ($q) {
                $q->orderBy('attendance_date', 'desc')->limit(10);
            },
            'payrollItems' => function ($q) {
                $q->with('payroll')->latest()->limit(6);
            }
        ]);

        return view('hr::employees.show', compact('employee'));
    }

    /**
     * Show the form for editing the specified employee.
     */
    public function edit(Employee $employee)
    {
        // Get users who are NOT linked to ANY employee OR are linked to THIS employee
        $users = User::doesntHave('employee')
            ->orWhere('id', $employee->user_id)
            ->get();

        return view('hr::employees.edit', compact('employee', 'users'));
    }

    /**
     * Update the specified employee in storage.
     */
    public function update(Request $request, Employee $employee)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'birth_date' => 'nullable|date',
            'gender' => 'nullable|string|max:20',
            'nationality' => 'nullable|string|max:255',
            'marital_status' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'position' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'basic_salary' => 'required|numeric|min:0',
            'bank_name' => 'nullable|string|max:255',
            'bank_account_number' => 'nullable|string|max:255',
            'iban' => 'nullable|string|max:255',
            'social_security_number' => 'nullable|string|max:255',
            'contract_type' => 'nullable|string|max:50',
            'status' => 'required|in:active,inactive,on_leave,terminated',
            'user_id' => 'nullable|exists:users,id|unique:hr_employees,user_id,' . $employee->id,
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
        ]);

        try {
            $employee->update([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'birth_date' => $request->birth_date,
                'gender' => $request->gender,
                'nationality' => $request->nationality,
                'marital_status' => $request->marital_status,
                'email' => $request->email,
                'phone' => $request->phone,
                'position' => $request->position,
                'department' => $request->department,
                'basic_salary' => $request->basic_salary,
                'bank_name' => $request->bank_name,
                'bank_account_number' => $request->bank_account_number,
                'iban' => $request->iban,
                'social_security_number' => $request->social_security_number,
                'contract_type' => $request->contract_type,
                'status' => $request->status,
                'user_id' => $request->user_id,
                'date_of_joining' => $request->date_of_joining,
                'id_number' => $request->id_number,
                'address' => $request->address,
                'emergency_contact_name' => $request->emergency_contact_name,
                'emergency_contact_phone' => $request->emergency_contact_phone,
            ]);

            // Handle Delivery Driver Logic
            if ($request->has('is_driver') && $request->is_driver == 1) {
                // Validate Driver Fields
                $request->validate([
                    'license_number' => 'required|string|max:50',
                    'license_expiry' => 'nullable|date',
                    'vehicle_type' => 'nullable|string|max:50',
                    'vehicle_plate' => 'nullable|string|max:20',
                ]);

                \Modules\HR\Models\DeliveryDriver::updateOrCreate(
                    ['employee_id' => $employee->id],
                    [
                        'license_number' => $request->license_number,
                        'license_expiry' => $request->license_expiry,
                        'vehicle_info' => ($request->vehicle_type . ' - ' . $request->vehicle_plate),
                        // Keep existing status/rating or default if new
                        'status' => $employee->deliveryDriver?->status ?? \Modules\HR\Enums\DeliveryDriverStatus::AVAILABLE,
                        'rating' => $employee->deliveryDriver?->rating ?? 5.0,
                        'total_deliveries' => $employee->deliveryDriver?->total_deliveries ?? 0,
                    ]
                );
            } else {
                // If is_driver is unchecked, we might want to delete the driver profile or just leave it?
                // For now, let's leave it but maybe disable it? Or delete it if user explicitly unchecks?
                // Safer to just leave it to prevent accidental data loss, or we can soft delete if supported.
                // Requirement implies "form fields are empty/not present", so if unticked, we assume not a driver.
                // But deleting might lose history. Let's just leave it for now.
            }

            return redirect()->route('hr.employees.index')
                ->with('success', 'تم تحديث بيانات الموظف بنجاح.');
        } catch (\Exception $e) {
            return back()->with('error', 'حدث خطأ أثناء تحديث الموظف: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified employee from storage.
     */
    public function destroy(Employee $employee)
    {
        try {
            $employee->delete();
            return redirect()->route('hr.employees.index')
                ->with('success', 'تم حذف الموظف بنجاح.');
        } catch (\Exception $e) {
            return back()->with('error', 'حدث خطأ أثناء حذف الموظف: ' . $e->getMessage());
        }
    }
}
