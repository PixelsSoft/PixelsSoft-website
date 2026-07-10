@extends('admin.layout')

@section('title', $project->name . ' — Tasks')

@section('content')
<div class="card">
    <div class="card-header">
        <h2>{{ $project->name }} — Task Board</h2>
        <div>
            <a href="{{ route('admin.pm.projects.show', $project) }}" class="btn btn-sm btn-outline">Project Details</a>
            @can('pm.tasks.create')
                <a href="{{ route('admin.pm.projects.tasks.create', $project) }}" class="btn btn-primary">+ New Task</a>
            @endcan
        </div>
    </div>

    @php
        $statusLabels = [
            'backlog' => 'Backlog',
            'todo' => 'To Do',
            'in_progress' => 'In Progress',
            'review' => 'Review',
            'done' => 'Done',
        ];
        $statusColors = [
            'backlog' => '#9ca3af',
            'todo' => '#3b82f6',
            'in_progress' => '#f59e0b',
            'review' => '#8b5cf6',
            'done' => '#10b981',
        ];
    @endphp

    <div class="kanban-board">
        @foreach($statusLabels as $status => $label)
            <div class="kanban-column" data-status="{{ $status }}">
                <div class="kanban-column-header" style="border-top:3px solid {{ $statusColors[$status] }}">
                    <strong>{{ $label }}</strong>
                    <span class="badge badge-draft">{{ $tasksByStatus[$status]->count() }}</span>
                </div>
                <div class="kanban-cards" data-status="{{ $status }}">
                    @foreach($tasksByStatus[$status] as $task)
                        <div class="kanban-card" draggable="true" data-task-id="{{ $task->id }}">
                            <strong>{{ $task->title }}</strong>
                            @if($task->assignee)<small>{{ $task->assignee->name }}</small>@endif
                            <div class="kanban-card-meta">
                                <span>{{ $task->priority }}</span>
                                @can('pm.tasks.edit')
                                    <a href="{{ route('admin.pm.projects.tasks.edit', [$project, $task]) }}">Edit</a>
                                @endcan
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</div>

@can('pm.tasks.edit')
<script>
document.querySelectorAll('.kanban-card').forEach(card => {
    card.addEventListener('dragstart', e => {
        e.dataTransfer.setData('task-id', card.dataset.taskId);
        card.classList.add('dragging');
    });
    card.addEventListener('dragend', () => card.classList.remove('dragging'));
});

document.querySelectorAll('.kanban-cards').forEach(column => {
    column.addEventListener('dragover', e => { e.preventDefault(); column.classList.add('drag-over'); });
    column.addEventListener('dragleave', () => column.classList.remove('drag-over'));
    column.addEventListener('drop', async e => {
        e.preventDefault();
        column.classList.remove('drag-over');
        const taskId = e.dataTransfer.getData('task-id');
        const status = column.dataset.status;
        const token = '{{ csrf_token() }}';
        await fetch(`/admin/pm/projects/{{ $project->id }}/tasks/${taskId}/status`, {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
            body: JSON.stringify({ status })
        });
        location.reload();
    });
});
</script>
@endcan
@endsection
