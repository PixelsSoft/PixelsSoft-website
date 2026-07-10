@extends('admin.layout')

@section('title', $contact->exists ? 'Edit Contact' : 'New Contact')

@section('content')
<div class="card">
    <div class="card-header"><h2>{{ $contact->exists ? 'Edit Contact' : 'Create Contact' }}</h2></div>
    <form method="POST" action="{{ $contact->exists ? route('admin.crm.contacts.update', $contact) : route('admin.crm.contacts.store') }}">
        @csrf
        @if($contact->exists) @method('PUT') @endif
        <div class="form-grid">
            <div class="form-group"><label>Name *</label><input type="text" name="name" value="{{ old('name', $contact->name) }}" required></div>
            <div class="form-group"><label>Email</label><input type="email" name="email" value="{{ old('email', $contact->email) }}"></div>
            <div class="form-group"><label>Phone</label><input type="text" name="phone" value="{{ old('phone', $contact->phone) }}"></div>
            <div class="form-group"><label>Job Title</label><input type="text" name="job_title" value="{{ old('job_title', $contact->job_title) }}"></div>
            <div class="form-group">
                <label>Company</label>
                <select name="company_id"><option value="">— None —</option>
                    @foreach($companies as $company)
                        <option value="{{ $company->id }}" @selected(old('company_id', $contact->company_id) == $company->id)>{{ $company->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label><input type="checkbox" name="is_primary" value="1" @checked(old('is_primary', $contact->is_primary))> Primary contact</label>
            </div>
        </div>
        <div class="form-actions">
            <a href="{{ route('admin.crm.contacts.index') }}" class="btn btn-outline">Cancel</a>
            <button type="submit" class="btn btn-primary">Save Contact</button>
        </div>
    </form>
</div>
@endsection
