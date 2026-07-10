@extends('admin.layout')

@section('title', $task->exists ? 'Edit Task' : 'New Task')

@section('content')
<div class="card">
    <div class="card-header">
        <h2>{{ $task->exists ? 'Edit Task' : 'Create Task' }}</h2>
        <small style="color:#6b7280">Project: {{ $project->name }}</small>
    </div>
    <form method="POST" action="{{ $task->exists ? route('admin.pm.projects.tasks.update', [$project, $task]) : route('admin.pm.projects.tasks.store', $project) }}">
        @csrf
        @if($task->exists) @method('PUT') @endif
        <div class="form-grid">
            <div class="form-group"><label>Title *</label><input type="text" name="title" value="{{ old('title', $task->title) }}" required></div>
            <div class="form-group">
                <label>Status *</label>
                <select name="status" required>
                    @foreach(['backlog','todo','in_progress','review','done'] as $status)
                        <option value="{{ $status }}" @selected(old('status', $task->status ?? 'backlog') === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Priority *</label>
                <select name="priority" required>
                    @foreach(['low','medium','high','urgent'] as $priority)
                        <option value="{{ $priority }}" @selected(old('priority', $task->priority ?? 'medium') === $priority)>{{ ucfirst($priority) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Assignee</label>
                <select name="assignee_id"><option value="">— Unassigned —</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" @selected(old('assignee_id', $task->assignee_id) == $user->id)>{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group"><label>Due Date</label><input type="date" name="due_date" value="{{ old('due_date', $task->due_date?->format('Y-m-d')) }}"></div>
            <div class="form-group"><label>Estimated Hours</label><input type="number" step="0.25" min="0" name="estimated_hours" value="{{ old('estimated_hours', $task->estimated_hours) }}"></div>
        </div>
        <div class="form-group" style="margin-top:16px"><label>Description</label><textarea name="description" rows="4">{{ old('description', $task->description) }}</textarea></div>
        <div class="form-actions">
            <a href="{{ route('admin.pm.projects.tasks.kanban', $project) }}" class="btn btn-outline">Cancel</a>
            @if($task->exists)
                @can('pm.tasks.delete')
                    <button type="button" class="btn btn-outline" onclick="if(confirm('Delete this task?')) document.getElementById('delete-task-form').submit()">Delete</button>
                @endcan
            @endif
            <button type="submit" class="btn btn-primary">Save Task</button>
        </div>
    </form>
    @if($task->exists)
        @can('pm.tasks.delete')
            <form id="delete-task-form" action="{{ route('admin.pm.projects.tasks.destroy', [$project, $task]) }}" method="POST" style="display:none">
                @csrf @method('DELETE')
            </form>
        @endcan
    @endif
</div>

@if($task->exists)
<div class="card" style="margin-top:24px">
    <div class="card-header"><h2>Comments</h2></div>
    @forelse($task->comments as $comment)
        <div style="padding:12px 20px;border-bottom:1px solid #e5e7eb">
            <strong>{{ $comment->user?->name ?? 'User' }}</strong>
            <small style="color:#6b7280;margin-left:8px">{{ $comment->created_at->diffForHumans() }}</small>
            <p style="margin:8px 0 0">{{ $comment->body }}</p>
        </div>
    @empty
        <p style="padding:20px;color:#6b7280">No comments yet.</p>
    @endforelse
    @can('pm.tasks.edit')
    <form method="POST" action="{{ route('admin.pm.projects.tasks.comments.store', [$project, $task]) }}" style="padding:20px">
        @csrf
        <div class="form-group">
            <label>Add comment</label>
            <textarea name="body" rows="3" required placeholder="Write a comment..."></textarea>
        </div>
        <button type="submit" class="btn btn-primary btn-sm">Post Comment</button>
    </form>
    @endcan
</div>
@endif
@endsection
