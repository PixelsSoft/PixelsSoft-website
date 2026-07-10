@extends('admin.layout')

@section('title', 'Request Leave')

@section('content')
<div class="card">
    <div class="card-header"><h2>Request Leave</h2></div>

    @if(!$employee)
        <div class="empty-state" style="padding:24px">
            <p>No employee record is linked to your account. Contact HR to set up your profile before requesting leave.</p>
            <a href="{{ route('admin.hr.leave.index') }}" class="btn btn-outline" style="margin-top:12px">Back</a>
        </div>
    @else
        <form method="POST" action="{{ route('admin.hr.leave.store') }}">
            @csrf
            <div style="padding:0 24px 8px;color:#6b7280">Requesting as: <strong>{{ $employee->user?->name ?? $employee->employee_code }}</strong></div>
            <div class="form-grid">
                <div class="form-group">
                    <label>Leave Type *</label>
                    <select name="leave_type_id" required>
                        <option value="">— Select —</option>
                        @foreach($leaveTypes as $type)
                            <option value="{{ $type->id }}" @selected(old('leave_type_id', $leaveRequest->leave_type_id) == $type->id)>{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group"><label>Start Date *</label><input type="date" name="start_date" value="{{ old('start_date', $leaveRequest->start_date?->format('Y-m-d')) }}" required></div>
                <div class="form-group"><label>End Date *</label><input type="date" name="end_date" value="{{ old('end_date', $leaveRequest->end_date?->format('Y-m-d')) }}" required></div>
                <div class="form-group"><label>Days *</label><input type="number" step="0.5" min="0.5" name="days" value="{{ old('days', $leaveRequest->days) }}" required></div>
            </div>
            <div class="form-group" style="margin-top:16px"><label>Reason</label><textarea name="reason" rows="3">{{ old('reason', $leaveRequest->reason) }}</textarea></div>
            <div class="form-actions">
                <a href="{{ route('admin.hr.leave.index') }}" class="btn btn-outline">Cancel</a>
                <button type="submit" class="btn btn-primary">Submit Request</button>
            </div>
        </form>
    @endif
</div>
@endsection
