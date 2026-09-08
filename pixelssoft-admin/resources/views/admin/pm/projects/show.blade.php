@extends('admin.layout')

@section('title', $project->name)

@section('content')
<div class="card">
    <div class="card-header">
        <h2>{{ $project->name }} <small style="font-weight:normal;color:#6b7280">({{ $project->code }})</small></h2>
        <div>
            @can('pm.tasks.view')
                <a href="{{ route('admin.pm.projects.tasks.kanban', $project) }}" class="btn btn-sm btn-primary">Task Board</a>
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
    <div class="form-grid" style="padding:0 24px 24px">
        <div><strong>Status:</strong> {{ str_replace('_', ' ', $project->status) }}</div>
        <div><strong>Priority:</strong> {{ $project->priority }}</div>
        <div><strong>Company:</strong> {{ $project->company?->name ?? '—' }}</div>
        <div><strong>Manager:</strong> {{ $project->manager?->name ?? '—' }}</div>
        <div><strong>Timeline:</strong> {{ $project->start_date?->format('M d, Y') ?? '—' }} → {{ $project->due_date?->format('M d, Y') ?? '—' }}</div>
        <div><strong>Budget hours:</strong> {{ $project->budget_hours ?: '—' }}</div>
    </div>
    @if($project->description)
        <div style="padding:0 24px 24px"><strong>Description:</strong><p style="margin-top:8px;color:#6b7280">{{ $project->description }}</p></div>
    @endif
</div>

<div class="card">
    <div class="card-header"><h2>Delivery team</h2></div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Person</th><th>Role</th>@can('pm.members.manage')<th></th>@endcan</tr></thead>
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
                    <tr><td colspan="{{ auth()->user()->can('pm.members.manage') ? 3 : 2 }}" class="empty-state">No team members yet. Add developers, QA, and production here.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @can('pm.members.manage')
        <form method="POST" action="{{ route('admin.pm.projects.members.store', $project) }}" style="padding:0 24px 24px">
            @csrf
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
                <button type="submit" class="btn btn-primary">Add member</button>
            </div>
        </form>
    @endcan
</div>

<div class="card">
    <div class="card-header">
        <h2>Tasks ({{ $project->tasks->count() }})</h2>
        @can('pm.tasks.create')
            <a href="{{ route('admin.pm.projects.tasks.create', $project) }}" class="btn btn-sm btn-primary">+ New Task</a>
        @endcan
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Title</th><th>Status</th><th>Priority</th><th>Assignee</th><th>Due</th><th>Actions</th></tr></thead>
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
@endsection
