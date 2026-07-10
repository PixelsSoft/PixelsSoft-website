@extends('admin.layout')

@section('title', 'Log Time')

@section('content')
<div class="card">
    <div class="card-header"><h2>Log Time Entry</h2></div>
    <form method="POST" action="{{ route('admin.pm.time.store') }}">
        @csrf
        <div class="form-grid">
            <div class="form-group">
                <label>Project *</label>
                <select name="project_id" id="project_id" required>
                    <option value="">— Select —</option>
                    @foreach($projects as $project)
                        <option value="{{ $project->id }}" @selected(old('project_id', $entry->project_id) == $project->id)>{{ $project->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Task</label>
                <select name="task_id" id="task_id">
                    <option value="">— None —</option>
                    @foreach($tasks as $task)
                        <option value="{{ $task->id }}" data-project="{{ $task->project_id }}" @selected(old('task_id', $entry->task_id) == $task->id)>{{ $task->title }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group"><label>Date *</label><input type="date" name="date" value="{{ old('date', $entry->date?->format('Y-m-d') ?? now()->format('Y-m-d')) }}" required></div>
            <div class="form-group"><label>Hours *</label><input type="number" step="0.25" min="0.25" max="24" name="hours" value="{{ old('hours', $entry->hours) }}" required></div>
            <div class="form-group" style="display:flex;align-items:center;gap:8px;padding-top:28px">
                <input type="checkbox" name="billable" id="billable" value="1" @checked(old('billable', $entry->billable ?? true))>
                <label for="billable" style="margin:0">Billable</label>
            </div>
        </div>
        <div class="form-group" style="margin-top:16px"><label>Description</label><textarea name="description" rows="3" maxlength="500">{{ old('description', $entry->description) }}</textarea></div>
        <div class="form-actions">
            <a href="{{ route('admin.pm.time.index') }}" class="btn btn-outline">Cancel</a>
            <button type="submit" class="btn btn-primary">Save Entry</button>
        </div>
    </form>
</div>

<script>
document.getElementById('project_id')?.addEventListener('change', function () {
    const projectId = this.value;
    document.querySelectorAll('#task_id option[data-project]').forEach(opt => {
        if (!opt.value) return;
        opt.hidden = projectId && opt.dataset.project !== projectId;
    });
});
document.getElementById('project_id')?.dispatchEvent(new Event('change'));
</script>
@endsection
