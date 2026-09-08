@extends('admin.layout')

@section('title', 'Projects')

@section('content')
<div class="card">
    <div class="card-header">
        <h2>Projects</h2>
        @can('pm.projects.create')
            <a href="{{ route('admin.pm.projects.create') }}" class="btn btn-primary">+ New Project</a>
        @endcan
    </div>

    @include('admin.partials.list-toolbar', [
        'showSearch' => true,
        'searchPlaceholder' => 'Search projects…',
        'filters' => [
            [
                'name' => 'status',
                'label' => 'All statuses',
                'options' => [
                    'planning' => 'Planning',
                    'active' => 'Active',
                    'on_hold' => 'On hold',
                    'completed' => 'Completed',
                    'cancelled' => 'Cancelled',
                ],
            ],
        ],
    ])

    <div class="table-wrap">
        <table>
            <thead><tr><th>Name</th><th>Code</th><th>Company</th><th>Status</th><th>Manager</th><th>Actions</th></tr></thead>
            <tbody>
                @forelse($projects as $project)
                    <tr>
                        <td><a href="{{ route('admin.pm.projects.show', $project) }}"><strong>{{ $project->name }}</strong></a></td>
                        <td>{{ $project->code }}</td>
                        <td>{{ $project->company?->name ?? '—' }}</td>
                        <td><span class="badge badge-draft">{{ str_replace('_', ' ', $project->status) }}</span></td>
                        <td>{{ $project->manager?->name ?? '—' }}</td>
                        <td class="table-actions">
                            <a href="{{ route('admin.pm.projects.show', $project) }}" class="btn btn-sm btn-outline">View</a>
                            @can('pm.tasks.view')
                                <a href="{{ route('admin.pm.projects.tasks.kanban', $project) }}" class="btn btn-sm btn-outline">Tasks</a>
                            @endcan
                            @can('pm.projects.edit')
                                <a href="{{ route('admin.pm.projects.edit', $project) }}" class="btn btn-sm btn-outline">Edit</a>
                            @endcan
                            @can('pm.projects.delete')
                                <form action="{{ route('admin.pm.projects.destroy', $project) }}" method="POST" onsubmit="return confirm('Delete?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="empty-state">No projects yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $projects->links() }}
</div>
@endsection
