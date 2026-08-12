<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Http\Controllers\Controller;
use App\Models\Hr\Employee;
use App\Models\Hr\LeaveBalance;
use App\Models\Hr\LeaveRequest;
use App\Models\Hr\LeaveType;
use Illuminate\Http\Request;

class LeaveRequestAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = LeaveRequest::with(['employee.user', 'leaveType'])
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%' . $request->string('q') . '%';
                $q->where(function ($inner) use ($term) {
                    $inner->where('reason', 'like', $term)
                        ->orWhere('status', 'like', $term)
                        ->orWhereHas('leaveType', fn ($t) => $t->where('name', 'like', $term))
                        ->orWhereHas('employee.user', fn ($u) => $u->where('name', 'like', $term))
                        ->orWhereHas('employee', fn ($e) => $e->where('employee_code', 'like', $term));
                });
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')));

        if (auth()->user()->can('hr.leave.approve')) {
            $requests = $query->latest()->paginate(20)->withQueryString();
        } else {
            $employee = Employee::where('user_id', auth()->id())->first();
            $requests = $employee
                ? $query->where('employee_id', $employee->id)->latest()->paginate(20)->withQueryString()
                : LeaveRequest::whereRaw('1=0')->paginate(20)->withQueryString();
        }

        return view('admin.hr.leave.index', compact('requests'));
    }

    public function create()
    {
        $employee = Employee::where('user_id', auth()->id())->first();

        return view('admin.hr.leave.form', [
            'leaveRequest' => new LeaveRequest(['status' => 'pending']),
            'leaveTypes' => LeaveType::orderBy('name')->get(),
            'employee' => $employee,
        ]);
    }

    public function store(Request $request)
    {
        $employee = Employee::where('user_id', auth()->id())->first();
        if (!$employee) {
            return back()->with('error', 'No employee record linked to your account.');
        }

        $data = $request->validate([
            'leave_type_id' => 'required|exists:hr_leave_types,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'days' => 'required|numeric|min:0.5',
            'reason' => 'nullable|string',
        ]);

        $data['employee_id'] = $employee->id;
        $data['status'] = 'pending';
        LeaveRequest::create($data);

        return redirect()->route('admin.hr.leave.index')->with('success', 'Leave request submitted.');
    }

    public function approve(LeaveRequest $leaveRequest)
    {
        $leaveRequest->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        $balance = LeaveBalance::firstOrCreate(
            [
                'employee_id' => $leaveRequest->employee_id,
                'leave_type_id' => $leaveRequest->leave_type_id,
                'year' => $leaveRequest->start_date->year,
            ],
            ['entitled' => $leaveRequest->leaveType->days_per_year, 'used' => 0]
        );

        $balance->increment('used', $leaveRequest->days);

        return back()->with('success', 'Leave request approved.');
    }

    public function reject(LeaveRequest $leaveRequest)
    {
        $leaveRequest->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Leave request rejected.');
    }
}
