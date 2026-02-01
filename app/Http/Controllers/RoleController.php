<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $roles = Role::withCount('users')->paginate(10);
        return view('settings.roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Group permissions by category for the matrix view
        $permissions = Permission::all()->groupBy(function ($perm) {
            $parts = explode('.', $perm->name);
            return $parts[0] ?? 'other';
        });

        return view('settings.roles.create', compact('permissions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:roles,name|max:255',
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,name'
        ]);

        $role = Role::create(['name' => $validated['name']]);
        $role->syncPermissions($validated['permissions']);

        return redirect()->route('roles.index')
            ->with('success', 'تم إنشاء الدور الوظيفي بنجاح');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Role $role)
    {
        // Prevent editing Super Admin
        if ($role->name === 'admin') {
            return back()->with('error', 'لا يمكن تعديل صلاحيات المدير العام');
        }

        $permissions = Permission::all()->groupBy(function ($perm) {
            $parts = explode('.', $perm->name);
            return $parts[0] ?? 'other';
        });

        return view('settings.roles.edit', compact('role', 'permissions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Role $role)
    {
        if ($role->name === 'admin') {
            return back()->with('error', 'لا يمكن تعديل صلاحيات المدير العام');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,name'
        ]);

        $role->update(['name' => $validated['name']]);
        $role->syncPermissions($validated['permissions']);

        return redirect()->route('roles.index')
            ->with('success', 'تم تحديث الدور الوظيفي بنجاح');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        if (in_array($role->name, ['admin', 'manager', 'cashier'])) {
            // Optional: Protect default roles? Or just Admin?
            if ($role->name === 'admin')
                return back()->with('error', 'لا يمكن حذف هذا الدور');
        }

        if ($role->users()->count() > 0) {
            return back()->with('error', 'لا يمكن حذف دور مستخدم حالياً من قبل موظفين');
        }

        $role->delete();
        return redirect()->route('roles.index')
            ->with('success', 'تم حذف الدور الوظيفي بنجاح');
    }
}
