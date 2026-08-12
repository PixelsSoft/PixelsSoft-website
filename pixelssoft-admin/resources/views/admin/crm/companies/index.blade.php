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

    @include('admin.partials.list-toolbar', [
        'showSearch' => true,
        'searchPlaceholder' => 'Search companies…',
    ])

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
                        <td class="table-actions">
                            <a href="{{ route('admin.crm.companies.show', $company) }}" class="btn btn-sm btn-outline">View</a>
                            @can('crm.companies.edit')
                                <a href="{{ route('admin.crm.companies.edit', $company) }}" class="btn btn-sm btn-outline">Edit</a>
                            @endcan
                            @can('crm.companies.delete')
                                <form action="{{ route('admin.crm.companies.destroy', $company) }}" method="POST" onsubmit="return confirm('Delete?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                </form>
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
