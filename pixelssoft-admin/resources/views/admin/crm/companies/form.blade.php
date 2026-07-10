@extends('admin.layout')

@section('title', $company->exists ? 'Edit Company' : 'New Company')

@section('content')
<div class="card">
    <div class="card-header"><h2>{{ $company->exists ? 'Edit Company' : 'Create Company' }}</h2></div>
    <form method="POST" action="{{ $company->exists ? route('admin.crm.companies.update', $company) : route('admin.crm.companies.store') }}">
        @csrf
        @if($company->exists) @method('PUT') @endif
        <div class="form-grid">
            <div class="form-group"><label>Name *</label><input type="text" name="name" value="{{ old('name', $company->name) }}" required></div>
            <div class="form-group"><label>Industry</label><input type="text" name="industry" value="{{ old('industry', $company->industry) }}"></div>
            <div class="form-group"><label>Website</label><input type="url" name="website" value="{{ old('website', $company->website) }}"></div>
            <div class="form-group"><label>Phone</label><input type="text" name="phone" value="{{ old('phone', $company->phone) }}"></div>
            <div class="form-group">
                <label>Status</label>
                <select name="status">
                    <option value="active" @selected(old('status', $company->status ?? 'active') === 'active')>Active</option>
                    <option value="inactive" @selected(old('status', $company->status) === 'inactive')>Inactive</option>
                </select>
            </div>
            <div class="form-group">
                <label>Owner</label>
                <select name="owner_id"><option value="">— None —</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" @selected(old('owner_id', $company->owner_id) == $user->id)>{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="form-group" style="margin-top:16px"><label>Address</label><textarea name="address" rows="2">{{ old('address', $company->address) }}</textarea></div>
        <div class="form-group"><label>Notes</label><textarea name="notes" rows="3">{{ old('notes', $company->notes) }}</textarea></div>
        <div class="form-actions">
            <a href="{{ route('admin.crm.companies.index') }}" class="btn btn-outline">Cancel</a>
            <button type="submit" class="btn btn-primary">Save Company</button>
        </div>
    </form>
</div>
@endsection
