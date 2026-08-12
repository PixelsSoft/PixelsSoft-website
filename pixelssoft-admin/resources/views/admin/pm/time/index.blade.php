@extends('admin.layout')

@section('title', 'Time Entries')

@section('content')
<div class="card">
    <div class="card-header">
        <h2>Time Entries</h2>
        @can('pm.time.view-own')
            <a href="{{ route('admin.pm.time.create') }}" class="btn btn-primary">+ Log Time</a>
        @endcan
    </div>

    @include('admin.partials.list-toolbar', [
        'showSearch' => true,
        'searchPlaceholder' => 'Search time entries…',
        'filters' => [
            [
                'name' => 'status',
                'label' => 'All statuses',
                'options' => [
                    'pending' => 'Pending',
                    'approved' => 'Approved',
                ],
            ],
        ],
    ])

    <div class="table-wrap">
        <table>
            <thead><tr><th>Date</th><th>Project</th><th>Task</th>@can('pm.time.view-all')<th>User</th>@endcan<th>Hours</th><th>Billable</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
                @forelse($entries as $entry)
                    <tr>
                        <td>{{ $entry->date->format('M d, Y') }}</td>
                        <td>{{ $entry->project?->name ?? '—' }}</td>
                        <td>{{ $entry->task?->title ?? '—' }}</td>
                        @can('pm.time.view-all')
                            <td>{{ $entry->user?->name ?? '—' }}</td>
                        @endcan
                        <td>{{ number_format($entry->hours, 2) }}</td>
                        <td>{{ $entry->billable ? 'Yes' : 'No' }}</td>
                        <td>
                            @if($entry->isApproved())
                                <span class="badge score-hot">Approved</span>
                            @else
                                <span class="badge badge-draft">Pending</span>
                            @endif
                        </td>
                        <td class="actions">
                            @can('pm.time.approve')
                                @if(!$entry->isApproved())
                                    <form action="{{ route('admin.pm.time.approve', $entry) }}" method="POST" style="display:inline">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-primary">Approve</button>
                                    </form>
                                @endif
                            @endcan
                            @can('pm.time.view-own')
                                <form action="{{ route('admin.pm.time.destroy', $entry) }}" method="POST" onsubmit="return confirm('Delete this entry?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="{{ auth()->user()->can('pm.time.view-all') ? 8 : 7 }}" class="empty-state">No time entries yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $entries->links() }}
</div>
@endsection
