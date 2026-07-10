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
        <div><strong>Deal:</strong> {{ $project->deal?->title ?? '—' }}</div>
        <div><strong>Manager:</strong> {{ $project->manager?->name ?? '—' }}</div>
        <div><strong>Timeline:</strong> {{ $project->start_date?->format('M d, Y') ?? '—' }} → {{ $project->due_date?->format('M d, Y') ?? '—' }}</div>
        <div><strong>Budget Hours:</strong> {{ $project->budget_hours ?? '—' }}</div>
        <div><strong>Budget Amount:</strong> @if($project->budget_amount) ${{ number_format($project->budget_amount, 2) }} @else — @endif</div>
    </div>
    @if($project->description)
        <div style="padding:0 24px 24px"><strong>Description:</strong><p style="margin-top:8px;color:#6b7280">{{ $project->description }}</p></div>
    @endif
    @if($project->budget_hours > 0)
        @php $burnPct = $project->budgetBurnPercent(); @endphp
        <div style="padding:0 24px 24px">
            <strong>Budget Burn:</strong>
            <div style="margin-top:8px">
                <div style="display:flex;justify-content:space-between;font-size:13px;color:#6b7280;margin-bottom:6px">
                    <span>{{ number_format($project->loggedHours(), 1) }} / {{ number_format($project->budget_hours, 1) }} hours</span>
                    <span>{{ $burnPct }}%</span>
                </div>
                <div style="height:12px;border-radius:6px;background:#e5e7eb;overflow:hidden">
                    <div style="height:100%;width:{{ $burnPct }}%;background:{{ $burnPct >= 80 ? '#ef4444' : ($burnPct >= 60 ? '#f59e0b' : '#10b981') }};border-radius:6px"></div>
                </div>
            </div>
        </div>
    @endif
</div>

<div class="card">
    <div class="card-header">
        <h2>Milestones ({{ $project->milestones->count() }})</h2>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Title</th><th>Due Date</th><th>Amount</th><th>Status</th>@can('pm.projects.edit')<th>Actions</th>@endcan</tr></thead>
            <tbody>
                @forelse($project->milestones as $milestone)
                    <tr>
                        <td><strong>{{ $milestone->title }}</strong></td>
                        <td>{{ $milestone->due_date?->format('M d, Y') ?? '—' }}</td>
                        <td>@if($milestone->amount) ${{ number_format($milestone->amount, 2) }} @else — @endif</td>
                        <td><span class="badge badge-draft">{{ str_replace('_', ' ', $milestone->status) }}</span></td>
                        @can('pm.projects.edit')
                            <td class="actions">
                                <form action="{{ route('admin.pm.projects.milestones.destroy', [$project, $milestone]) }}" method="POST" style="display:inline" onsubmit="return confirm('Remove this milestone?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline">Remove</button>
                                </form>
                            </td>
                        @endcan
                    </tr>
                @empty
                    <tr><td colspan="{{ auth()->user()->can('pm.projects.edit') ? 5 : 4 }}" class="empty-state">No milestones yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @can('pm.projects.edit')
        <form method="POST" action="{{ route('admin.pm.projects.milestones.store', $project) }}" style="padding:0 24px 24px">
            @csrf
            <h3 style="margin:16px 0 12px;font-size:15px">Add Milestone</h3>
            <div class="form-grid">
                <div class="form-group"><label>Title *</label><input type="text" name="title" required maxlength="255"></div>
                <div class="form-group"><label>Due Date</label><input type="date" name="due_date"></div>
                <div class="form-group"><label>Amount</label><input type="number" step="0.01" min="0" name="amount"></div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status">
                        @foreach(['pending', 'in_progress', 'completed'] as $status)
                            <option value="{{ $status }}">{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-actions" style="margin-top:12px;padding-top:0;border-top:none">
                <button type="submit" class="btn btn-primary">Add Milestone</button>
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
