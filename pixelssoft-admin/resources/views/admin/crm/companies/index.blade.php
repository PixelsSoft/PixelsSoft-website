@extends('admin.layout')

@section('title', 'Companies')

@section('content')
<div class="card">
    <div class="card-header">
        <h2>Companies</h2>
        @can('crm.companies.create')
            <a href="{{ route('admin.crm.companies.create') }}" class="btn btn-primary">+ New Company</a>
        @endcan
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Name</th><th>Industry</th><th>Contacts</th><th>Deals</th><th>Owner</th><th>Actions</th></tr></thead>
            <tbody>
                @forelse($companies as $company)
                    <tr>
                        <td><a href="{{ route('admin.crm.companies.show', $company) }}"><strong>{{ $company->name }}</strong></a></td>
                        <td>{{ $company->industry ?? '—' }}</td>
                        <td>{{ $company->contacts_count }}</td>
                        <td>{{ $company->deals_count }}</td>
                        <td>{{ $company->owner?->name ?? '—' }}</td>
                        <td>
                            @can('crm.companies.edit')
                                <a href="{{ route('admin.crm.companies.edit', $company) }}" class="btn btn-sm btn-outline">Edit</a>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="empty-state">No companies yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $companies->links() }}
</div>
@endsection
