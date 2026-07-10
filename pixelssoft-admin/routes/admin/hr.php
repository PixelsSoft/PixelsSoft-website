<?php

use App\Http\Controllers\Admin\Hr\AttendanceAdminController;
use App\Http\Controllers\Admin\Hr\DepartmentAdminController;
use App\Http\Controllers\Admin\Hr\EmployeeAdminController;
use App\Http\Controllers\Admin\Hr\HrDashboardController;
use App\Http\Controllers\Admin\Hr\HrDocumentAdminController;
use App\Http\Controllers\Admin\Hr\HrPayrollAdminController;
use App\Http\Controllers\Admin\Hr\HrReportController;
use App\Http\Controllers\Admin\Hr\LeaveRequestAdminController;
use Illuminate\Support\Facades\Route;

Route::prefix('hr')->name('hr.')->group(function () {
    Route::middleware('permission:hr.dashboard.view')->get('/', [HrDashboardController::class, 'index'])->name('dashboard');

    Route::middleware('permission:hr.employees.view')->get('/employees', [EmployeeAdminController::class, 'index'])->name('employees.index');
    Route::middleware('permission:hr.employees.create')->group(function () {
        Route::get('/employees/create', [EmployeeAdminController::class, 'create'])->name('employees.create');
        Route::post('/employees', [EmployeeAdminController::class, 'store'])->name('employees.store');
    });
    Route::middleware('permission:hr.employees.edit')->group(function () {
        Route::get('/employees/{employee}/edit', [EmployeeAdminController::class, 'edit'])->name('employees.edit');
        Route::put('/employees/{employee}', [EmployeeAdminController::class, 'update'])->name('employees.update');
    });
    Route::middleware('permission:hr.employees.delete')->delete('/employees/{employee}', [EmployeeAdminController::class, 'destroy'])->name('employees.destroy');

    Route::middleware('role_or_permission:hr.leave.view-own|hr.leave.approve')->get('/leave', [LeaveRequestAdminController::class, 'index'])->name('leave.index');
    Route::middleware('permission:hr.leave.request')->group(function () {
        Route::get('/leave/create', [LeaveRequestAdminController::class, 'create'])->name('leave.create');
        Route::post('/leave', [LeaveRequestAdminController::class, 'store'])->name('leave.store');
    });
    Route::middleware('permission:hr.leave.approve')->group(function () {
        Route::patch('/leave/{leaveRequest}/approve', [LeaveRequestAdminController::class, 'approve'])->name('leave.approve');
        Route::patch('/leave/{leaveRequest}/reject', [LeaveRequestAdminController::class, 'reject'])->name('leave.reject');
    });

    Route::middleware('role_or_permission:hr.attendance.view|hr.attendance.manage')->get('/attendance', [AttendanceAdminController::class, 'index'])->name('attendance.index');
    Route::middleware('permission:hr.attendance.manage')->post('/attendance', [AttendanceAdminController::class, 'store'])->name('attendance.store');

    Route::middleware('permission:hr.documents.view')->get('/documents', [HrDocumentAdminController::class, 'index'])->name('documents.index');
    Route::middleware('permission:hr.documents.manage')->group(function () {
        Route::post('/documents', [HrDocumentAdminController::class, 'store'])->name('documents.store');
        Route::delete('/documents/{document}', [HrDocumentAdminController::class, 'destroy'])->name('documents.destroy');
    });

    Route::middleware('permission:hr.payroll.view')->get('/payroll', [HrPayrollAdminController::class, 'index'])->name('payroll.index');
    Route::middleware('permission:hr.payroll.process')->group(function () {
        Route::post('/payroll', [HrPayrollAdminController::class, 'store'])->name('payroll.store');
        Route::get('/payroll/{payrollRun}', [HrPayrollAdminController::class, 'show'])->name('payroll.show');
        Route::patch('/payroll/{payrollRun}/process', [HrPayrollAdminController::class, 'process'])->name('payroll.process');
        Route::get('/payroll/{payrollRun}/items/{item}/payslip', [HrPayrollAdminController::class, 'payslip'])->name('payroll.payslip');
    });

    Route::middleware('permission:hr.employees.view')->get('/departments', [DepartmentAdminController::class, 'index'])->name('departments.index');
    Route::middleware('permission:hr.employees.edit')->group(function () {
        Route::post('/departments', [DepartmentAdminController::class, 'store'])->name('departments.store');
        Route::put('/departments/{department}', [DepartmentAdminController::class, 'update'])->name('departments.update');
        Route::delete('/departments/{department}', [DepartmentAdminController::class, 'destroy'])->name('departments.destroy');
    });

    Route::middleware('permission:hr.dashboard.view')->get('/reports', [HrReportController::class, 'index'])->name('reports.index');
});
