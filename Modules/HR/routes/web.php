<?php

use Illuminate\Support\Facades\Route;
use Modules\HR\Http\Controllers\HRController;
use Modules\HR\Http\Controllers\EmployeeController;
use Modules\HR\Http\Controllers\AttendanceController;
use Modules\HR\Http\Controllers\PayrollController;
use Modules\HR\Http\Controllers\DeliveryController;

Route::prefix('hr')->name('hr.')->middleware(['auth', 'verified'])->group(function () {
    Route::get('/', [HRController::class, 'index'])->name('dashboard');
    Route::resource('employees', EmployeeController::class);

    // Attendance
    Route::get('attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('attendance/check-in', [AttendanceController::class, 'checkIn'])->name('attendance.check-in');
    Route::post('attendance/check-out', [AttendanceController::class, 'checkOut'])->name('attendance.check-out');
    Route::post('attendance/manual', [AttendanceController::class, 'storeManual'])->name('attendance.manual-log');

    // Payroll
    Route::get('payroll', [PayrollController::class, 'index'])->name('payroll.index');
    Route::get('payroll/{payroll}', [PayrollController::class, 'show'])->name('payroll.show');
    Route::post('payroll/generate', [PayrollController::class, 'generate'])->name('payroll.generate');
    Route::post('payroll/{payroll}/post', [PayrollController::class, 'post'])->name('payroll.post');
    Route::put('payroll/items/{item}', [PayrollController::class, 'updateItem'])->name('payroll.items.update');

    // Delivery
    Route::resource('delivery', DeliveryController::class);
    Route::post('delivery/{driver}/status', [DeliveryController::class, 'updateStatus'])->name('delivery.status');

    // Documents
    Route::post('employees/{employee}/documents', [\Modules\HR\Http\Controllers\DocumentController::class, 'store'])->name('employees.documents.store');
    Route::get('documents/{document}/download', [\Modules\HR\Http\Controllers\DocumentController::class, 'download'])->name('documents.download');
    Route::delete('documents/{document}', [\Modules\HR\Http\Controllers\DocumentController::class, 'destroy'])->name('documents.destroy');

    // Leaves
    Route::post('employees/{employee}/leaves', [\Modules\HR\Http\Controllers\LeaveController::class, 'store'])->name('employees.leaves.store');
    Route::post('leaves/{leave}/approve', [\Modules\HR\Http\Controllers\LeaveController::class, 'approve'])->name('leaves.approve');
    Route::post('leaves/{leave}/reject', [\Modules\HR\Http\Controllers\LeaveController::class, 'reject'])->name('leaves.reject');

    // Reports
    Route::get('reports', [\Modules\HR\Http\Controllers\ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/generate', [\Modules\HR\Http\Controllers\ReportController::class, 'generate'])->name('reports.generate');
});
