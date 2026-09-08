@extends('admin.layout')

@section('title', $project->name)

@section('content')
<div data-ui-tabs>
    <div class="page-header">
        <div class="page-header-main">
            <div class="page-kicker">Project</div>
            <h1>{{ $project->name }}</h1>
            <div class="page-header-meta">
                <span class="badge badge-draft">{{ str_replace('_', ' ', $project->status) }}</span>
                <span class="form-meta">{{ $project->code }} · {{ $project->priority }}</span>
            </div>
        </div>
        <div class="page-header-actions">
            @can('pm.tasks.view')
                <a href="{{ route('admin.pm.projects.tasks.kanban', $project) }}" class="btn btn-sm btn-accent">Task board</a>
            @endcan
            @can('pm.projects.edit')
                <a href="{{ route('admin.pm.projects.edit', $project) }}" class="btn btn-sm btn-outline">Edit</a>
            @endcan
            @can('pm.projects.delete')
                <form action="{{ route('admin.pm.projects.destroy', $project) }}" method="POST" style="display:inline" onsubmit="return confirm('Delete this project?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline">Delete</button>
                </form>
            @endcan
        </div>
    </div>

    <div class="ui-tabs" role="tablist">
        <button type="button" class="ui-tab is-active" data-tab="overview" role="tab">Overview</button>
        <button type="button" class="ui-tab" data-tab="team" role="tab">Team ({{ $project->members->count() }})</button>
        <button type="button" class="ui-tab" data-tab="tasks" role="tab">Tasks ({{ $project->tasks->count() }})</button>
    </div>

    <div class="ui-tab-panel is-active" data-panel="overview">
        <div class="card">
            <div class="card-header"><h2>Details</h2></div>
            <div class="dl-grid">
                <div><span class="dl-label">Status</span><div class="dl-value">{{ str_replace('_', ' ', $project->status) }}</div></div>
                <div><span class="dl-label">Priority</span><div class="dl-value">{{ $project->priority }}</div></div>
                <div><span class="dl-label">Company</span><div class="dl-value">{{ $project->company?->name ?? '—' }}</div></div>
                <div><span class="dl-label">Manager</span><div class="dl-value">{{ $project->manager?->name ?? '—' }}</div></div>
                <div><span class="dl-label">Timeline</span><div class="dl-value">{{ $project->start_date?->format('M d, Y') ?? '—' }} → {{ $project->due_date?->format('M d, Y') ?? '—' }}</div></div>
                <div><span class="dl-label">Budget hours</span><div class="dl-value">{{ $project->budget_hours ?: '—' }}</div></div>
            </div>
            @if($project->description)
                <div style="margin-top:14px;padding-top:12px;border-top:1px solid var(--border)">
                    <span class="dl-label">Description</span>
                    <p style="margin-top:6px;color:#6b7280;font-size:14px">{{ $project->description }}</p>
                </div>
            @endif
        </div>
    </div>

    <div class="ui-tab-panel" data-panel="team">
        <div class="table-card">
            <div class="card-header"><h2>Delivery team</h2></div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Person</th>
                            <th>Role</th>
                            @can('pm.members.manage')<th></th>@endcan
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($project->members as $member)
                            <tr>
                                <td>{{ $member->user?->name ?? '—' }}</td>
                                <td>{{ ucfirst($member->role) }}</td>
                                @can('pm.members.manage')
                                    <td>
                                        <form action="{{ route('admin.pm.projects.members.destroy', [$project, $member]) }}" method="POST" onsubmit="return confirm('Remove this member?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline">Remove</button>
                                        </form>
                                    </td>
                                @endcan
                            </tr>
                        @empty
                            <tr><td colspan="{{ auth()->user()->can('pm.members.manage') ? 3 : 2 }}" class="empty-state">No team members yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @can('pm.members.manage')
                <form method="POST" action="{{ route('admin.pm.projects.members.store', $project) }}" class="inline-form form-narrow">
                    @csrf
                    <h3>Add member</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Person</label>
                            <select name="user_id" required>
                                <option value="">Select…</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Role</label>
                            <select name="role" required>
                                @foreach($memberRoles as $role)
                                    <option value="{{ $role }}">{{ ucfirst($role) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-actions" style="margin-top:12px;padding-top:0;border-top:none">
                        <button type="submit" class="btn btn-accent">Add member</button>
                    </div>
                </form>
            @endcan
        </div>
    </div>

    <div class="ui-tab-panel" data-panel="tasks">
        <div class="table-card">
            <div class="card-header">
                <h2>Tasks</h2>
                @can('pm.tasks.create')
                    <a href="{{ route('admin.pm.projects.tasks.create', $project) }}" class="btn btn-sm btn-accent">+ New task</a>
                @endcan
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Status</th>
                            <th>Priority</th>
                            <th>Assignee</th>
                            <th>Due</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($project->tasks as $task)
                            <tr>
                                <td><strong>{{ $task->title }}</strong></td>
                                <td><span class="badge badge-draft">{{ str_replace('_', ' ', $task->status) }}</span></td>
                                <td>{{ $task->priority }}</td>
                                <td>{{ $task->assignee?->name ?? '—' }}</td>
                                <td>{{ $task->due_date?->format('M d, Y') ?? '—' }}</td>
                                <td class="actions">
                                    @can('pm.tasks.edit')
                                        <a href="{{ route('admin.pm.projects.tasks.edit', [$project, $task]) }}" class="btn btn-sm btn-outline">Edit</a>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="empty-state">No tasks yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
