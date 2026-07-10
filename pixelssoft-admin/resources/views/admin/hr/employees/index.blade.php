@extends('admin.layout')

@section('title', 'Employees')

@section('content')
<div class="card">
    <div class="card-header">
        <h2>Employees</h2>
        @can('hr.employees.create')
            <a href="{{ route('admin.hr.employees.create') }}" class="btn btn-primary">+ New Employee</a>
        @endcan
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Code</th><th>Name</th><th>Department</th><th>Position</th><th>Type</th><th>Join Date</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
                @forelse($employees as $employee)
                    <tr>
                        <td><strong>{{ $employee->employee_code }}</strong></td>
                        <td>{{ $employee->user?->name ?? '—' }}</td>
                        <td>{{ $employee->department?->name ?? '—' }}</td>
                        <td>{{ $employee->position ?? '—' }}</td>
                        <td>{{ $employee->employment_type }}</td>
                        <td>{{ $employee->join_date?->format('M d, Y') ?? '—' }}</td>
                        <td><span class="badge badge-draft">{{ $employee->status }}</span></td>
                        <td class="actions">
                            @can('hr.employees.edit')
                                <a href="{{ route('admin.hr.employees.edit', $employee) }}" class="btn btn-sm btn-outline">Edit</a>
                            @endcan
                            @can('hr.employees.delete')
                                <form action="{{ route('admin.hr.employees.destroy', $employee) }}" method="POST" style="display:inline" onsubmit="return confirm('Delete this employee?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline">Delete</button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="empty-state">No employees yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $employees->links() }}
</div>
@endsection
