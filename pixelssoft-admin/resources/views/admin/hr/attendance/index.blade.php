@extends('admin.layout')

@section('title', 'Attendance')

@section('content')
@can('hr.attendance.manage')
    @php $employees = \App\Models\Hr\Employee::with('user')->where('status', 'active')->orderBy('employee_code')->get(); @endphp
    <div class="card">
        <div class="card-header"><h2>Record Attendance</h2></div>
        <form method="POST" action="{{ route('admin.hr.attendance.store') }}">
            @csrf
            <div class="form-grid">
                <div class="form-group">
                    <label>Employee *</label>
                    <select name="employee_id" required>
                        <option value="">— Select —</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}" @selected(old('employee_id') == $employee->id)>{{ $employee->user?->name ?? $employee->employee_code }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group"><label>Date *</label><input type="date" name="date" value="{{ old('date', now()->format('Y-m-d')) }}" required></div>
                <div class="form-group"><label>Check In</label><input type="time" name="check_in" value="{{ old('check_in') }}"></div>
                <div class="form-group"><label>Check Out</label><input type="time" name="check_out" value="{{ old('check_out') }}"></div>
                <div class="form-group">
                    <label>Status *</label>
                    <select name="status" required>
                        @foreach(['present','absent','late','wfh'] as $status)
                            <option value="{{ $status }}" @selected(old('status', 'present') === $status)>{{ strtoupper($status) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-group" style="margin-top:16px"><label>Notes</label><textarea name="notes" rows="2">{{ old('notes') }}</textarea></div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Save Attendance</button>
            </div>
        </form>
    </div>
@endcan

<div class="card">
    <div class="card-header">
        <h2>Attendance Records</h2>
        @cannot('hr.attendance.manage')
            <small style="color:#6b7280">Your records only</small>
        @endcannot
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    @can('hr.attendance.manage')<th>Employee</th>@endcan
                    <th>Check In</th>
                    <th>Check Out</th>
                    <th>Status</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $record)
                    <tr>
                        <td>{{ $record->date->format('M d, Y') }}</td>
                        @can('hr.attendance.manage')
                            <td>{{ $record->employee?->user?->name ?? $record->employee?->employee_code ?? '—' }}</td>
                        @endcan
                        <td>{{ $record->check_in ?? '—' }}</td>
                        <td>{{ $record->check_out ?? '—' }}</td>
                        <td><span class="badge badge-draft">{{ strtoupper($record->status) }}</span></td>
                        <td>{{ Str::limit($record->notes, 40) ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="{{ auth()->user()->can('hr.attendance.manage') ? 6 : 5 }}" class="empty-state">No attendance records yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $records->links() }}
</div>
@endsection
