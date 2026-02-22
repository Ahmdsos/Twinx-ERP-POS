<?php

use Illuminate\Support\Facades\Route;
use Modules\HR\Http\Controllers\EmployeeController;
use Modules\HR\Http\Controllers\PayrollController;
use Modules\HR\Http\Controllers\LeaveController;
use Modules\HR\Http\Controllers\AttendanceController;
use Modules\HR\Http\Controllers\HRController;
use Modules\HR\Http\Controllers\DeliveryController;
use Modules\HR\Http\Controllers\DocumentController;
use Modules\HR\Http\Controllers\ReportController;

Route::prefix('hr')->name('hr.')->middleware(['auth'])->group(function () {

    Route::get('/', [HRController::class, 'index'])->name('dashboard');

    // ==========================================
    // EMPLOYEES
    // ==========================================
    Route::middleware(['can:hr.employees.manage'])->group(function () {
        Route::resource('employees', EmployeeController::class)->except(['index', 'show']);
    });

    Route::middleware(['can:hr.employees.view'])->group(function () {
        Route::get('employees', [EmployeeController::class, 'index'])->name('employees.index');
        Route::get('employees/{employee}', [EmployeeController::class, 'show'])->name('employees.show');
    });

    // ==========================================
    // PAYROLL
    // ==========================================
    Route::middleware(['can:hr.payroll.view'])->group(function () {
        Route::get('payroll', [PayrollController::class, 'index'])->name('payroll.index');
        Route::get('payroll/{payroll}', [PayrollController::class, 'show'])->name('payroll.show');
    });

    Route::middleware(['can:hr.payroll.manage'])->group(function () {
        Route::get('payroll/create', [PayrollController::class, 'create'])->name('payroll.create');
        Route::post('payroll', [PayrollController::class, 'store'])->name('payroll.store');
        Route::get('payroll/{payroll}/edit', [PayrollController::class, 'edit'])->name('payroll.edit');
        Route::put('payroll/{payroll}', [PayrollController::class, 'update'])->name('payroll.update');
        Route::delete('payroll/{payroll}', [PayrollController::class, 'destroy'])->name('payroll.destroy');

        // Actions
        Route::post('payroll/generate', [PayrollController::class, 'generate'])->name('payroll.generate');
        Route::post('payroll/{payroll}/post', [PayrollController::class, 'post'])->name('payroll.post');
        Route::post('payroll/{payroll}/recalculate', [PayrollController::class, 'recalculate'])->name('payroll.recalculate');
        Route::put('payroll/items/{item}', [PayrollController::class, 'updateItem'])->name('payroll.items.update');
    });

    // ==========================================
    // LEAVES
    // ==========================================
    Route::middleware(['can:hr.leave.view'])->group(function () {
        Route::get('leaves', [LeaveController::class, 'index'])->name('leaves.index');
    });

    Route::middleware(['can:hr.leave.manage'])->group(function () {
        Route::post('leaves/{leave}/approve', [LeaveController::class, 'approve'])->name('leaves.approve');
        Route::post('leaves/{leave}/reject', [LeaveController::class, 'reject'])->name('leaves.reject');
        Route::post('employees/{employee}/leaves', [LeaveController::class, 'store'])->name('leaves.store');
        Route::post('employees/{employee}/leaves', [LeaveController::class, 'store'])->name('leaves.store');
    });

    // ==========================================
    // ADVANCES
    // ==========================================
    Route::middleware(['can:hr.advances.view'])->group(function () {
        Route::get('advances', [\Modules\HR\Http\Controllers\AdvanceController::class, 'index'])->name('advances.index');
        Route::get('advances/create', [\Modules\HR\Http\Controllers\AdvanceController::class, 'create'])->name('advances.create');
        Route::get('advances/{advance}', [\Modules\HR\Http\Controllers\AdvanceController::class, 'show'])->name('advances.show');
    });

    Route::middleware(['can:hr.advances.manage'])->group(function () {
        Route::post('advances', [\Modules\HR\Http\Controllers\AdvanceController::class, 'store'])->name('advances.store');
        Route::post('advances/{advance}/approve', [\Modules\HR\Http\Controllers\AdvanceController::class, 'approve'])->name('advances.approve');
        Route::post('advances/{advance}/pay', [\Modules\HR\Http\Controllers\AdvanceController::class, 'pay'])->name('advances.pay');
    });

    // ==========================================
    // ATTENDANCE & OTHER
    // ==========================================
    Route::get('attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('attendance/check-in', [AttendanceController::class, 'checkIn'])->name('attendance.check-in');
    Route::post('attendance/check-out', [AttendanceController::class, 'checkOut'])->name('attendance.check-out');
    Route::post('attendance/manual', [AttendanceController::class, 'storeManual'])->name('attendance.manual-log');

    Route::resource('delivery', DeliveryController::class)->parameters(['delivery' => 'driver']);

    Route::post('employees/{employee}/documents', [DocumentController::class, 'store'])->name('employees.documents.store');
    Route::get('documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');
    Route::delete('documents/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');

    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::post('reports/generate', [ReportController::class, 'generate'])->name('reports.generate');
});
