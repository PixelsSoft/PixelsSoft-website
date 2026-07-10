<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Http\Controllers\Controller;
use App\Models\Hr\Attendance;
use App\Models\Hr\Employee;
use Illuminate\Http\Request;

class AttendanceAdminController extends Controller
{
    public function index()
    {
        $query = Attendance::with('employee.user')->latest('date');

        if (!auth()->user()->can('hr.attendance.manage')) {
            $employee = Employee::where('user_id', auth()->id())->first();
            $query = $employee ? $query->where('employee_id', $employee->id) : $query->whereRaw('1=0');
        }

        $records = $query->paginate(30);

        return view('admin.hr.attendance.index', compact('records'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'employee_id' => 'required|exists:hr_employees,id',
            'date' => 'required|date',
            'check_in' => 'nullable|date_format:H:i',
            'check_out' => 'nullable|date_format:H:i',
            'status' => 'required|in:present,absent,late,wfh',
            'notes' => 'nullable|string',
        ]);

        Attendance::updateOrCreate(
            ['employee_id' => $data['employee_id'], 'date' => $data['date']],
            $data
        );

        return back()->with('success', 'Attendance recorded.');
    }
}
