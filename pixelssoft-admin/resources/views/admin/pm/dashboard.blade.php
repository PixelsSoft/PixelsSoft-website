@extends('admin.layout')

@section('title', 'PM Dashboard')

@section('content')
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-label">Total Projects</div>
        <div class="stat-value">{{ $stats['projects'] }}</div>
        <small style="color:#6b7280">{{ $stats['active_projects'] }} active</small>
    </div>
    <div class="stat-card">
        <div class="stat-label">Open Tasks</div>
        <div class="stat-value">{{ $stats['open_tasks'] }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">My Tasks</div>
        <div class="stat-value">{{ $stats['my_tasks'] }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Pending Time</div>
        <div class="stat-value">{{ $stats['pending_time'] }}</div>
        <small style="color:#6b7280">awaiting approval</small>
    </div>
</div>

<div class="quick-actions">
    @can('pm.projects.create')
        <a href="{{ route('admin.pm.projects.create') }}" class="quick-action"><div class="qa-icon">+</div><div><strong>New Project</strong></div></a>
    @endcan
    @can('pm.time.view-own')
        <a href="{{ route('admin.pm.time.create') }}" class="quick-action"><div class="qa-icon">⏱</div><div><strong>Log Time</strong></div></a>
    @endcan
    <a href="{{ route('admin.pm.projects.index') }}" class="quick-action"><div class="qa-icon">▦</div><div><strong>All Projects</strong></div></a>
</div>

<div class="card">
    <div class="card-header">
        <h2>Recent Projects</h2>
        <a href="{{ route('admin.pm.projects.index') }}" class="btn btn-sm btn-outline">View All</a>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Project</th><th>Code</th><th>Status</th><th>Manager</th><th>Due</th></tr></thead>
            <tbody>
                @forelse($recentProjects as $project)
                    <tr>
                        <td><a href="{{ route('admin.pm.projects.show', $project) }}"><strong>{{ $project->name }}</strong></a></td>
                        <td>{{ $project->code }}</td>
                        <td><span class="badge badge-draft">{{ str_replace('_', ' ', $project->status) }}</span></td>
                        <td>{{ $project->manager?->name ?? '—' }}</td>
                        <td>{{ $project->due_date?->format('M d, Y') ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="empty-state">No projects yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2>My Tasks</h2>
        <a href="{{ route('admin.pm.time.index') }}" class="btn btn-sm btn-outline">Time Entries</a>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Task</th><th>Project</th><th>Status</th><th>Priority</th><th>Due</th></tr></thead>
            <tbody>
                @forelse($myTasks as $task)
                    <tr>
                        <td>
                            @can('pm.tasks.edit')
                                <a href="{{ route('admin.pm.projects.tasks.edit', [$task->project, $task]) }}">{{ $task->title }}</a>
                            @else
                                {{ $task->title }}
                            @endcan
                        </td>
                        <td>
                            @can('pm.projects.view')
                                <a href="{{ route('admin.pm.projects.show', $task->project) }}">{{ $task->project?->name ?? '—' }}</a>
                            @else
                                {{ $task->project?->name ?? '—' }}
                            @endcan
                        </td>
                        <td><span class="badge badge-draft">{{ str_replace('_', ' ', $task->status) }}</span></td>
                        <td><span class="badge score-{{ $task->priority === 'urgent' ? 'hot' : ($task->priority === 'high' ? 'warm' : 'cold') }}">{{ $task->priority }}</span></td>
                        <td>{{ $task->due_date?->format('M d, Y') ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="empty-state">No open tasks assigned to you.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
