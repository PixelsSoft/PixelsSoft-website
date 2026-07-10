@extends('admin.layout')

@section('title', $company->name)

@section('content')
<div class="card">
    <div class="card-header">
        <h2>{{ $company->name }}</h2>
        @can('crm.companies.edit')
            <a href="{{ route('admin.crm.companies.edit', $company) }}" class="btn btn-sm btn-outline">Edit</a>
        @endcan
    </div>
    <div class="form-grid" style="padding:0 24px 24px">
        <div><strong>Industry:</strong> {{ $company->industry ?? '—' }}</div>
        <div><strong>Website:</strong> {{ $company->website ?? '—' }}</div>
        <div><strong>Phone:</strong> {{ $company->phone ?? '—' }}</div>
        <div><strong>Owner:</strong> {{ $company->owner?->name ?? '—' }}</div>
    </div>
</div>

<div class="card">
    <div class="card-header"><h2>Contacts ({{ $company->contacts->count() }})</h2></div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Job Title</th></tr></thead>
            <tbody>
                @forelse($company->contacts as $contact)
                    <tr><td>{{ $contact->name }}</td><td>{{ $contact->email }}</td><td>{{ $contact->phone }}</td><td>{{ $contact->job_title }}</td></tr>
                @empty
                    <tr><td colspan="4" class="empty-state">No contacts.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <div class="card-header"><h2>Deals ({{ $company->deals->count() }})</h2></div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Title</th><th>Stage</th><th>Value</th></tr></thead>
            <tbody>
                @forelse($company->deals as $deal)
                    <tr><td>{{ $deal->title }}</td><td>{{ $deal->stage?->name }}</td><td>${{ number_format($deal->value, 0) }}</td></tr>
                @empty
                    <tr><td colspan="3" class="empty-state">No deals.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
