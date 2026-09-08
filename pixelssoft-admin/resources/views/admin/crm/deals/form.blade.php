@extends('admin.layout')

@section('title', $deal->exists ? 'Edit Deal' : 'New Deal')

@section('content')
<div class="card">
    <div class="card-header"><h2>{{ $deal->exists ? 'Edit Deal' : 'Create Deal' }}</h2></div>
    <form method="POST" action="{{ $deal->exists ? route('admin.crm.deals.update', $deal) : route('admin.crm.deals.store') }}">
        @csrf
        @if($deal->exists) @method('PUT') @endif
        <div class="form-grid">
            <div class="form-group"><label>Title *</label><input type="text" name="title" value="{{ old('title', $deal->title) }}" required></div>
            <div class="form-group">
                <label>Pipeline *</label>
                <select name="pipeline_id" id="pipeline_id" required>
                    @foreach($pipelines as $pipe)
                        <option value="{{ $pipe->id }}" @selected(old('pipeline_id', $deal->pipeline_id ?? $pipeline?->id) == $pipe->id)>{{ $pipe->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Stage *</label>
                <select name="stage_id" required>
                    @foreach($pipeline?->stages ?? [] as $stage)
                        <option value="{{ $stage->id }}" @selected(old('stage_id', $deal->stage_id) == $stage->id)>{{ $stage->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group"><label>Value</label><input type="number" step="0.01" name="value" value="{{ old('value', $deal->value ?? 0) }}"></div>
            <div class="form-group">
                <label>Currency</label>
                <input type="text" name="currency" maxlength="3" value="{{ old('currency', $deal->currency ?? 'USD') }}">
            </div>
            <div class="form-group">
                <label>Source</label>
                <select name="source_id">
                    <option value="">— Select —</option>
                    @foreach($sources as $source)
                        <option value="{{ $source->id }}" @selected(old('source_id', $deal->source_id) == $source->id)>{{ $source->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Company</label>
                <select name="company_id"><option value="">— None —</option>
                    @foreach($companies as $company)
                        <option value="{{ $company->id }}" @selected(old('company_id', $deal->company_id) == $company->id)>{{ $company->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Owner</label>
                <input type="text" value="{{ $deal->exists ? ($deal->owner?->name ?? '—') : auth()->user()->name }}" disabled>
                <span class="settle-amount-hint">Owner is the person who created the lead. Commission stays with them.</span>
            </div>
            <div class="form-group"><label>Expected Close</label><input type="date" name="expected_close" value="{{ old('expected_close', $deal->expected_close?->format('Y-m-d')) }}"></div>
            <div class="form-group"><label>Portal / job URL</label><input type="url" name="portal_url" value="{{ old('portal_url', $deal->portal_url) }}" placeholder="https://www.upwork.com/…"></div>
            <div class="form-group"><label>Portal contract ID</label><input type="text" name="portal_contract_id" value="{{ old('portal_contract_id', $deal->portal_contract_id) }}"></div>
        </div>
        <div class="form-group" style="margin-top:16px"><label>Notes</label><textarea name="notes" rows="3">{{ old('notes', $deal->notes) }}</textarea></div>
        <div class="form-actions">
            <a href="{{ route('admin.crm.deals.kanban') }}" class="btn btn-outline">Cancel</a>
            <button type="submit" class="btn btn-primary">Save Deal</button>
        </div>
    </form>
</div>
@endsection
