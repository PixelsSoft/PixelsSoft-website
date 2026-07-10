<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Http\Controllers\Controller;
use App\Models\Hr\Attendance;
use App\Models\Hr\Employee;
use App\Models\Hr\LeaveRequest;

class HrDashboardController extends Controller
{
    public function index()
    {
        return view('admin.hr.dashboard', [
            'stats' => [
                'employees' => Employee::where('status', 'active')->count(),
                'departments' => \App\Models\Hr\Department::count(),
                'pending_leave' => LeaveRequest::where('status', 'pending')->count(),
                'present_today' => Attendance::whereDate('date', today())->where('status', 'present')->count(),
            ],
            'pendingLeave' => LeaveRequest::with(['employee.user', 'leaveType'])->where('status', 'pending')->latest()->take(5)->get(),
            'recentEmployees' => Employee::with(['department', 'user'])->latest()->take(5)->get(),
        ]);
    }
}
