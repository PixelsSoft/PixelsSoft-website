@extends('admin.layout')

@section('title', $lead->exists ? 'Edit Lead' : 'New Lead')

@section('content')
@php
    $ownerName = $lead->exists
        ? ($lead->owner?->name ?? '—')
        : auth()->user()->name;
@endphp
<div class="card">
    <div class="card-header"><h2>{{ $lead->exists ? 'Edit Lead' : 'Create Lead' }}</h2></div>
    <form method="POST" action="{{ $lead->exists ? route('admin.crm.leads.update', $lead) : route('admin.crm.leads.store') }}">
        @csrf
        @if($lead->exists) @method('PUT') @endif
        <div class="form-grid">
            <div class="form-group"><label>Title *</label><input type="text" name="title" value="{{ old('title', $lead->title) }}" required></div>
            <div class="form-group">
                <label>Company</label>
                <select name="company_id"><option value="">— None —</option>
                    @foreach($companies as $company)
                        <option value="{{ $company->id }}" @selected(old('company_id', $lead->company_id) == $company->id)>{{ $company->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Budget *</label>
                <input type="number" step="0.01" min="0" name="budget" value="{{ old('budget', $lead->budget) }}" required>
            </div>
            <div class="form-group">
                <label>Currency</label>
                <input type="text" name="currency" maxlength="3" value="{{ old('currency', $lead->currency ?? 'USD') }}">
            </div>
            <div class="form-group">
                <label>Status *</label>
                <select name="status" required>
                    @foreach(['new','contacted','qualified','converted','lost'] as $status)
                        <option value="{{ $status }}" @selected(old('status', $lead->status ?? 'new') === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Score *</label>
                <select name="score" required>
                    @foreach(['hot','warm','cold'] as $score)
                        <option value="{{ $score }}" @selected(old('score', $lead->score ?? 'warm') === $score)>{{ ucfirst($score) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Source</label>
                <select name="source_id">
                    <option value="">— Select —</option>
                    @foreach($sources as $source)
                        <option value="{{ $source->id }}" @selected(old('source_id', $lead->source_id) == $source->id)>{{ $source->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Owner</label>
                <input type="text" value="{{ $ownerName }}" disabled>
                <span class="settle-amount-hint">Assigned to whoever creates the lead. Sales commission follows this person.</span>
            </div>
            <div class="form-group"><label>Portal / job URL</label><input type="url" name="portal_url" value="{{ old('portal_url', $lead->portal_url) }}" placeholder="https://www.freelancer.com/projects/…"></div>
            <div class="form-group"><label>Portal contract ID</label><input type="text" name="portal_contract_id" value="{{ old('portal_contract_id', $lead->portal_contract_id) }}"></div>
        </div>
        <div class="form-group" style="margin-top:16px"><label>Notes</label><textarea name="notes" rows="4">{{ old('notes', $lead->notes) }}</textarea></div>
        <div class="form-actions">
            <a href="{{ route('admin.crm.leads.index') }}" class="btn btn-outline">Cancel</a>
            <button type="submit" class="btn btn-primary">Save Lead</button>
        </div>
    </form>
</div>
@endsection
