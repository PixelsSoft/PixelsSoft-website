<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Http\Controllers\Controller;
use App\Models\Hr\Attendance;
use App\Models\Hr\Employee;
use Illuminate\Http\Request;

class AttendanceAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = Attendance::with('employee.user')
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%' . $request->string('q') . '%';
                $q->where(function ($inner) use ($term) {
                    $inner->where('notes', 'like', $term)
                        ->orWhereHas('employee.user', fn ($u) => $u->where('name', 'like', $term))
                        ->orWhereHas('employee', fn ($e) => $e->where('employee_code', 'like', $term));
                });
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->latest('date');

        if (!auth()->user()->can('hr.attendance.manage')) {
            $employee = Employee::where('user_id', auth()->id())->first();
            $query = $employee ? $query->where('employee_id', $employee->id) : $query->whereRaw('1=0');
        }

        $records = $query->paginate(30)->withQueryString();

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
