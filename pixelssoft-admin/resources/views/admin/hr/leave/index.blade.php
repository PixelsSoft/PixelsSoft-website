@extends('admin.layout')

@section('title', 'Leave Requests')

@section('content')
<div class="card">
    <div class="card-header">
        <h2>Leave Requests</h2>
        @can('hr.leave.request')
            <a href="{{ route('admin.hr.leave.create') }}" class="btn btn-primary">+ Request Leave</a>
        @endcan
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    @can('hr.leave.approve')<th>Employee</th>@endcan
                    <th>Type</th>
                    <th>Start</th>
                    <th>End</th>
                    <th>Days</th>
                    <th>Status</th>
                    <th>Reason</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($requests as $request)
                    <tr>
                        @can('hr.leave.approve')
                            <td>{{ $request->employee?->user?->name ?? $request->employee?->employee_code ?? '—' }}</td>
                        @endcan
                        <td>{{ $request->leaveType?->name ?? '—' }}</td>
                        <td>{{ $request->start_date->format('M d, Y') }}</td>
                        <td>{{ $request->end_date->format('M d, Y') }}</td>
                        <td>{{ $request->days }}</td>
                        <td><span class="badge badge-draft">{{ $request->status }}</span></td>
                        <td>{{ Str::limit($request->reason, 50) ?? '—' }}</td>
                        <td class="actions">
                            @can('hr.leave.approve')
                                @if($request->status === 'pending')
                                    <form action="{{ route('admin.hr.leave.approve', $request) }}" method="POST" style="display:inline">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-primary">Approve</button>
                                    </form>
                                    <form action="{{ route('admin.hr.leave.reject', $request) }}" method="POST" style="display:inline">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-outline">Reject</button>
                                    </form>
                                @endif
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="{{ auth()->user()->can('hr.leave.approve') ? 8 : 7 }}" class="empty-state">No leave requests yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $requests->links() }}
</div>
@endsection
