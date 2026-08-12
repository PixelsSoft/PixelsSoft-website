<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Http\Controllers\Controller;
use App\Models\Hr\Department;
use App\Models\Hr\Employee;
use App\Models\User;
use Illuminate\Http\Request;

class EmployeeAdminController extends Controller
{
    public function index(Request $request)
    {
        $employees = Employee::with(['department', 'user'])
            ->when($request->filled('q'), function ($query) use ($request) {
                $q = '%' . $request->string('q') . '%';
                $query->where(function ($inner) use ($q) {
                    $inner->where('employee_code', 'like', $q)
                        ->orWhere('position', 'like', $q)
                        ->orWhere('phone', 'like', $q)
                        ->orWhereHas('user', fn ($u) => $u->where('name', 'like', $q)->orWhere('email', 'like', $q))
                        ->orWhereHas('department', fn ($d) => $d->where('name', 'like', $q));
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('employment_type'), fn ($query) => $query->where('employment_type', $request->string('employment_type')))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.hr.employees.index', compact('employees'));
    }

    public function create()
    {
        return view('admin.hr.employees.form', [
            'employee' => new Employee(['status' => 'active', 'employment_type' => 'full-time']),
            'departments' => Department::orderBy('name')->get(),
            'users' => User::where('status', 'active')->whereDoesntHave('employee')->orderBy('name')->get(),
            'managers' => Employee::where('status', 'active')->orderBy('employee_code')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['employee_code'] = Employee::generateCode();
        $employee = Employee::create($data);

        if ($employee->user_id) {
            User::where('id', $employee->user_id)->update(['employee_id' => $employee->id]);
        }

        foreach (\App\Models\Hr\LeaveType::all() as $type) {
            \App\Models\Hr\LeaveBalance::create([
                'employee_id' => $employee->id,
                'leave_type_id' => $type->id,
                'year' => now()->year,
                'entitled' => $type->days_per_year,
                'used' => 0,
            ]);
        }

        return redirect()->route('admin.hr.employees.index')->with('success', 'Employee created.');
    }

    public function edit(Employee $employee)
    {
        return view('admin.hr.employees.form', [
            'employee' => $employee,
            'departments' => Department::orderBy('name')->get(),
            'users' => User::where('status', 'active')->where(function ($q) use ($employee) {
                $q->whereDoesntHave('employee')->orWhere('id', $employee->user_id);
            })->orderBy('name')->get(),
            'managers' => Employee::where('status', 'active')->where('id', '!=', $employee->id)->orderBy('employee_code')->get(),
        ]);
    }

    public function update(Request $request, Employee $employee)
    {
        $data = $this->validated($request);
        $oldUserId = $employee->user_id;
        $employee->update($data);

        if ($oldUserId && $oldUserId != $employee->user_id) {
            User::where('id', $oldUserId)->update(['employee_id' => null]);
        }
        if ($employee->user_id) {
            User::where('id', $employee->user_id)->update(['employee_id' => $employee->id]);
        }

        return redirect()->route('admin.hr.employees.index')->with('success', 'Employee updated.');
    }

    public function destroy(Employee $employee)
    {
        if ($employee->user_id) {
            User::where('id', $employee->user_id)->update(['employee_id' => null]);
        }
        $employee->delete();

        return redirect()->route('admin.hr.employees.index')->with('success', 'Employee deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'department_id' => 'nullable|exists:hr_departments,id',
            'position' => 'nullable|string|max:100',
            'join_date' => 'nullable|date',
            'employment_type' => 'required|in:full-time,part-time,contract',
            'salary' => 'nullable|numeric|min:0',
            'manager_id' => 'nullable|exists:hr_employees,id',
            'status' => 'required|in:active,inactive',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
        ]);
    }
}
