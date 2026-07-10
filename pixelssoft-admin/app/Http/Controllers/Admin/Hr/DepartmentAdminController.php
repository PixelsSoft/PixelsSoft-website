<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Http\Controllers\Controller;
use App\Models\Hr\Department;
use App\Models\User;
use Illuminate\Http\Request;

class DepartmentAdminController extends Controller
{
    public function index()
    {
        $departments = Department::with('manager')->withCount('employees')->orderBy('name')->get();

        return view('admin.hr.departments.index', compact('departments'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:hr_departments,name',
            'manager_id' => 'nullable|exists:users,id',
        ]);

        Department::create($data);

        return back()->with('success', 'Department created.');
    }

    public function update(Request $request, Department $department)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:hr_departments,name,' . $department->id,
            'manager_id' => 'nullable|exists:users,id',
        ]);

        $department->update($data);

        return back()->with('success', 'Department updated.');
    }

    public function destroy(Department $department)
    {
        $department->delete();

        return back()->with('success', 'Department deleted.');
    }
}
