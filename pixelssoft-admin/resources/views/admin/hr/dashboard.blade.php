@extends('admin.layout')

@section('title', 'HR Dashboard')

@section('content')
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-label">Active Employees</div>
        <div class="stat-value">{{ $stats['employees'] }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Departments</div>
        <div class="stat-value">{{ $stats['departments'] }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Pending Leave</div>
        <div class="stat-value">{{ $stats['pending_leave'] }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Present Today</div>
        <div class="stat-value">{{ $stats['present_today'] }}</div>
    </div>
</div>

<div class="quick-actions">
    @can('hr.employees.create')
        <a href="{{ route('admin.hr.employees.create') }}" class="quick-action"><div class="qa-icon">+</div><div><strong>New Employee</strong></div></a>
    @endcan
    @can('hr.leave.request')
        <a href="{{ route('admin.hr.leave.create') }}" class="quick-action"><div class="qa-icon">📅</div><div><strong>Request Leave</strong></div></a>
    @endcan
    <a href="{{ route('admin.hr.attendance.index') }}" class="quick-action"><div class="qa-icon">✓</div><div><strong>Attendance</strong></div></a>
</div>

<div class="card">
    <div class="card-header">
        <h2>Pending Leave Requests</h2>
        <a href="{{ route('admin.hr.leave.index') }}" class="btn btn-sm btn-outline">View All</a>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Employee</th><th>Type</th><th>Dates</th><th>Days</th><th>Reason</th></tr></thead>
            <tbody>
                @forelse($pendingLeave as $request)
                    <tr>
                        <td>{{ $request->employee?->user?->name ?? $request->employee?->employee_code ?? '—' }}</td>
                        <td>{{ $request->leaveType?->name ?? '—' }}</td>
                        <td>{{ $request->start_date->format('M d') }} – {{ $request->end_date->format('M d, Y') }}</td>
                        <td>{{ $request->days }}</td>
                        <td>{{ Str::limit($request->reason, 40) ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="empty-state">No pending leave requests.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2>Recent Employees</h2>
        <a href="{{ route('admin.hr.employees.index') }}" class="btn btn-sm btn-outline">View All</a>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Code</th><th>Name</th><th>Department</th><th>Position</th><th>Status</th></tr></thead>
            <tbody>
                @forelse($recentEmployees as $employee)
                    <tr>
                        <td>{{ $employee->employee_code }}</td>
                        <td>{{ $employee->user?->name ?? '—' }}</td>
                        <td>{{ $employee->department?->name ?? '—' }}</td>
                        <td>{{ $employee->position ?? '—' }}</td>
                        <td><span class="badge badge-draft">{{ $employee->status }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="empty-state">No employees yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
