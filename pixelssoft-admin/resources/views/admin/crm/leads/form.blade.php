@extends('admin.layout')

@section('title', $lead->exists ? 'Edit Lead' : 'New Lead')

@section('content')
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
                <label>Contact</label>
                <select name="contact_id"><option value="">— None —</option>
                    @foreach($contacts as $contact)
                        <option value="{{ $contact->id }}" @selected(old('contact_id', $lead->contact_id) == $contact->id)>{{ $contact->name }}</option>
                    @endforeach
                </select>
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
                <input type="text" name="source" value="{{ old('source', $lead->source) }}" placeholder="website, referral, etc.">
            </div>
            <div class="form-group">
                <label>Owner</label>
                <select name="owner_id"><option value="">— Unassigned —</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" @selected(old('owner_id', $lead->owner_id) == $user->id)>{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="form-group" style="margin-top:16px"><label>Notes</label><textarea name="notes" rows="4">{{ old('notes', $lead->notes) }}</textarea></div>
        <div class="form-actions">
            <a href="{{ route('admin.crm.leads.index') }}" class="btn btn-outline">Cancel</a>
            <button type="submit" class="btn btn-primary">Save Lead</button>
        </div>
    </form>
</div>
@endsection
