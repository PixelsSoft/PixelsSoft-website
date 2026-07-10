@extends('admin.layout')

@section('title', $employee->exists ? 'Edit Employee' : 'New Employee')

@section('content')
<div class="card">
    <div class="card-header"><h2>{{ $employee->exists ? 'Edit Employee' : 'Create Employee' }}</h2></div>
    <form method="POST" action="{{ $employee->exists ? route('admin.hr.employees.update', $employee) : route('admin.hr.employees.store') }}">
        @csrf
        @if($employee->exists) @method('PUT') @endif
        <div class="form-grid">
            @if($employee->exists)
                <div class="form-group"><label>Employee Code</label><input type="text" value="{{ $employee->employee_code }}" disabled></div>
            @endif
            <div class="form-group">
                <label>User Account</label>
                <select name="user_id"><option value="">— None —</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" @selected(old('user_id', $employee->user_id) == $user->id)>{{ $user->name }} ({{ $user->email }})</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Department</label>
                <select name="department_id"><option value="">— None —</option>
                    @foreach($departments as $department)
                        <option value="{{ $department->id }}" @selected(old('department_id', $employee->department_id) == $department->id)>{{ $department->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group"><label>Position</label><input type="text" name="position" value="{{ old('position', $employee->position) }}"></div>
            <div class="form-group"><label>Join Date</label><input type="date" name="join_date" value="{{ old('join_date', $employee->join_date?->format('Y-m-d')) }}"></div>
            <div class="form-group">
                <label>Employment Type *</label>
                <select name="employment_type" required>
                    @foreach(['full-time','part-time','contract'] as $type)
                        <option value="{{ $type }}" @selected(old('employment_type', $employee->employment_type ?? 'full-time') === $type)>{{ ucfirst($type) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group"><label>Salary</label><input type="number" step="0.01" min="0" name="salary" value="{{ old('salary', $employee->salary) }}"></div>
            <div class="form-group">
                <label>Manager</label>
                <select name="manager_id"><option value="">— None —</option>
                    @foreach($managers as $manager)
                        <option value="{{ $manager->id }}" @selected(old('manager_id', $employee->manager_id) == $manager->id)>{{ $manager->user?->name ?? $manager->employee_code }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Status *</label>
                <select name="status" required>
                    @foreach(['active','inactive'] as $status)
                        <option value="{{ $status }}" @selected(old('status', $employee->status ?? 'active') === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group"><label>Phone</label><input type="text" name="phone" value="{{ old('phone', $employee->phone) }}"></div>
        </div>
        <div class="form-group" style="margin-top:16px"><label>Address</label><textarea name="address" rows="3">{{ old('address', $employee->address) }}</textarea></div>
        <div class="form-actions">
            <a href="{{ route('admin.hr.employees.index') }}" class="btn btn-outline">Cancel</a>
            <button type="submit" class="btn btn-primary">Save Employee</button>
        </div>
    </form>
</div>
@endsection
