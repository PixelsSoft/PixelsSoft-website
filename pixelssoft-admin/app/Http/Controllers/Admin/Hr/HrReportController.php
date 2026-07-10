<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Http\Controllers\Controller;
use App\Models\Hr\Attendance;
use App\Models\Hr\Department;
use App\Models\Hr\Employee;
use App\Models\Hr\LeaveRequest;
use Illuminate\Support\Facades\DB;

class HrReportController extends Controller
{
    public function index()
    {
        return view('admin.hr.reports.index', [
            'headcountByDept' => Employee::where('status', 'active')
                ->select('department_id', DB::raw('count(*) as count'))
                ->groupBy('department_id')
                ->with('department')
                ->get(),
            'leaveByType' => LeaveRequest::where('status', 'approved')
                ->select('leave_type_id', DB::raw('sum(days) as days'))
                ->groupBy('leave_type_id')
                ->with('leaveType')
                ->get(),
            'attendanceSummary' => Attendance::select('status', DB::raw('count(*) as count'))
                ->whereMonth('date', now()->month)
                ->groupBy('status')
                ->get(),
            'pendingLeave' => LeaveRequest::where('status', 'pending')->count(),
            'totalEmployees' => Employee::where('status', 'active')->count(),
            'departments' => Department::withCount('employees')->get(),
        ]);
    }
}
