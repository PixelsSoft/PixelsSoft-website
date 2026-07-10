@extends('admin.layout')

@section('title', 'Departments')

@section('content')
@php $users = \App\Models\User::orderBy('name')->get(['id', 'name']); @endphp

@can('hr.employees.edit')
<div class="card">
    <div class="card-header"><h2>Add Department</h2></div>
    <form method="POST" action="{{ route('admin.hr.departments.store') }}" style="padding:0 24px 24px">
        @csrf
        @include('admin.partials.errors')
        <div class="form-grid">
            <div class="form-group">
                <label>Name *</label>
                <input type="text" name="name" value="{{ old('name') }}" required maxlength="255">
            </div>
            <div class="form-group">
                <label>Manager</label>
                <select name="manager_id">
                    <option value="">— None —</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" @selected(old('manager_id') == $user->id)>{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="form-actions" style="margin-top:12px;padding-top:0;border-top:none">
            <button type="submit" class="btn btn-primary">Add Department</button>
        </div>
    </form>
</div>
@endcan

<div class="card">
    <div class="card-header"><h2>Departments</h2></div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Name</th><th>Manager</th><th>Employees</th>@can('hr.employees.edit')<th>Actions</th>@endcan</tr></thead>
            <tbody>
                @forelse($departments as $department)
                    <tr>
                        @can('hr.employees.edit')
                            <form id="dept-{{ $department->id }}" method="POST" action="{{ route('admin.hr.departments.update', $department) }}">
                                @csrf @method('PUT')
                            </form>
                            <td>
                                <input type="text" name="name" value="{{ $department->name }}" form="dept-{{ $department->id }}" required maxlength="255" style="width:100%">
                            </td>
                            <td>
                                <select name="manager_id" form="dept-{{ $department->id }}" style="width:100%">
                                    <option value="">— None —</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" @selected($department->manager_id == $user->id)>{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>{{ $department->employees_count }}</td>
                            <td class="actions">
                                <button type="submit" form="dept-{{ $department->id }}" class="btn btn-sm btn-primary">Save</button>
                                <form action="{{ route('admin.hr.departments.destroy', $department) }}" method="POST" style="display:inline" onsubmit="return confirm('Delete this department?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline">Delete</button>
                                </form>
                            </td>
                        @else
                            <td>{{ $department->name }}</td>
                            <td>{{ $department->manager?->name ?? '—' }}</td>
                            <td>{{ $department->employees_count }}</td>
                        @endcan
                    </tr>
                @empty
                    <tr><td colspan="{{ auth()->user()->can('hr.employees.edit') ? 4 : 3 }}" class="empty-state">No departments yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
