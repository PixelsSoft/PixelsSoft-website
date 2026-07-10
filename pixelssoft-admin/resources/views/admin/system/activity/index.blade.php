@extends('admin.layout')

@section('title', 'Activity Log')

@section('content')
<div class="card">
    <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;">
        <h2>Audit Trail</h2>
        <a href="{{ route('admin.system.activity.export') }}" class="btn btn-outline btn-sm">Export CSV</a>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr><th>When</th><th>User</th><th>Action</th><th>Subject</th><th>Changes</th></tr>
            </thead>
            <tbody>
                @forelse($activities as $activity)
                    <tr>
                        <td>{{ $activity->created_at->format('M d, Y H:i') }}</td>
                        <td>{{ $activity->causer?->name ?? 'System' }}</td>
                        <td>{{ $activity->description }}</td>
                        <td>{{ class_basename($activity->subject_type ?? '') }} #{{ $activity->subject_id }}</td>
                        <td><small style="color:#6b7280">{{ json_encode($activity->properties['attributes'] ?? []) }}</small></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="empty-state">No activity recorded yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $activities->links() }}
</div>
@endsection
