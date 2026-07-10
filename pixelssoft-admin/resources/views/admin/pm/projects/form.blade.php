@extends('admin.layout')

@section('title', $project->exists ? 'Edit Project' : 'New Project')

@section('content')
<div class="card">
    <div class="card-header"><h2>{{ $project->exists ? 'Edit Project' : 'Create Project' }}</h2></div>
    <form method="POST" action="{{ $project->exists ? route('admin.pm.projects.update', $project) : route('admin.pm.projects.store') }}">
        @csrf
        @if($project->exists) @method('PUT') @endif
        <div class="form-grid">
            <div class="form-group"><label>Name *</label><input type="text" name="name" value="{{ old('name', $project->name) }}" required></div>
            @if($project->exists)
                <div class="form-group"><label>Code</label><input type="text" value="{{ $project->code }}" disabled></div>
            @endif
            <div class="form-group">
                <label>Company</label>
                <select name="company_id"><option value="">— None —</option>
                    @foreach($companies as $company)
                        <option value="{{ $company->id }}" @selected(old('company_id', $project->company_id) == $company->id)>{{ $company->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Deal</label>
                <select name="deal_id"><option value="">— None —</option>
                    @foreach($deals as $deal)
                        <option value="{{ $deal->id }}" @selected(old('deal_id', $project->deal_id) == $deal->id)>{{ $deal->title }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Status *</label>
                <select name="status" required>
                    @foreach(['planning','active','on_hold','completed','archived'] as $status)
                        <option value="{{ $status }}" @selected(old('status', $project->status ?? 'planning') === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Priority *</label>
                <select name="priority" required>
                    @foreach(['low','medium','high','urgent'] as $priority)
                        <option value="{{ $priority }}" @selected(old('priority', $project->priority ?? 'medium') === $priority)>{{ ucfirst($priority) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group"><label>Start Date</label><input type="date" name="start_date" value="{{ old('start_date', $project->start_date?->format('Y-m-d')) }}"></div>
            <div class="form-group"><label>Due Date</label><input type="date" name="due_date" value="{{ old('due_date', $project->due_date?->format('Y-m-d')) }}"></div>
            <div class="form-group"><label>Budget Hours</label><input type="number" step="0.25" min="0" name="budget_hours" value="{{ old('budget_hours', $project->budget_hours) }}"></div>
            <div class="form-group"><label>Budget Amount</label><input type="number" step="0.01" min="0" name="budget_amount" value="{{ old('budget_amount', $project->budget_amount) }}"></div>
            <div class="form-group">
                <label>Manager</label>
                <select name="manager_id"><option value="">— Unassigned —</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" @selected(old('manager_id', $project->manager_id) == $user->id)>{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="form-group" style="margin-top:16px"><label>Description</label><textarea name="description" rows="4">{{ old('description', $project->description) }}</textarea></div>
        <div class="form-actions">
            <a href="{{ route('admin.pm.projects.index') }}" class="btn btn-outline">Cancel</a>
            <button type="submit" class="btn btn-primary">Save Project</button>
        </div>
    </form>
</div>
@endsection
