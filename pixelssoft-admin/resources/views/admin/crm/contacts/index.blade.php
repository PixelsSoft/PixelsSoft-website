@extends('admin.layout')

@section('title', 'Contacts')

@section('content')
<div class="card">
    <div class="card-header">
        <h2>Contacts</h2>
        @can('crm.contacts.create')
            <a href="{{ route('admin.crm.contacts.create') }}" class="btn btn-primary">+ New Contact</a>
        @endcan
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Company</th><th>Actions</th></tr></thead>
            <tbody>
                @forelse($contacts as $contact)
                    <tr>
                        <td><strong>{{ $contact->name }}</strong></td>
                        <td>{{ $contact->email ?? '—' }}</td>
                        <td>{{ $contact->phone ?? '—' }}</td>
                        <td>{{ $contact->company?->name ?? '—' }}</td>
                        <td>
                            @can('crm.contacts.edit')
                                <a href="{{ route('admin.crm.contacts.edit', $contact) }}" class="btn btn-sm btn-outline">Edit</a>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="empty-state">No contacts yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $contacts->links() }}
</div>
@endsection
