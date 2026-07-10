@extends('admin.layout')

@section('title', 'HR Reports')

@section('content')
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-label">Active Employees</div>
        <div class="stat-value">{{ $totalEmployees }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Departments</div>
        <div class="stat-value">{{ $departments->count() }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Pending Leave</div>
        <div class="stat-value">{{ $pendingLeave }}</div>
    </div>
</div>

<div class="card">
    <div class="card-header"><h2>Headcount by Department</h2></div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Department</th><th>Employees</th></tr></thead>
            <tbody>
                @forelse($headcountByDept as $row)
                    <tr>
                        <td>{{ $row->department?->name ?? 'Unassigned' }}</td>
                        <td>{{ $row->count }}</td>
                    </tr>
                @empty
                    <tr><td colspan="2" class="empty-state">No employee data.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px">
    <div class="card">
        <div class="card-header"><h2>Leave Usage by Type</h2></div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Leave Type</th><th>Days Used</th></tr></thead>
                <tbody>
                    @forelse($leaveByType as $row)
                        <tr>
                            <td>{{ $row->leaveType?->name ?? '—' }}</td>
                            <td>{{ number_format($row->days, 1) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="2" class="empty-state">No approved leave recorded.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h2>Attendance Summary (This Month)</h2></div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Status</th><th>Count</th></tr></thead>
                <tbody>
                    @forelse($attendanceSummary as $row)
                        <tr>
                            <td><span class="badge badge-draft">{{ str_replace('_', ' ', $row->status) }}</span></td>
                            <td>{{ $row->count }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="2" class="empty-state">No attendance records this month.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
