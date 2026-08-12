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

    @include('admin.partials.list-toolbar', [
        'showSearch' => true,
        'searchPlaceholder' => 'Search contacts…',
    ])

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
                        <td class="table-actions">
                            @can('crm.contacts.edit')
                                <a href="{{ route('admin.crm.contacts.edit', $contact) }}" class="btn btn-sm btn-outline">Edit</a>
                            @endcan
                            @if($contact->email)
                                <a href="mailto:{{ $contact->email }}" class="btn btn-sm btn-outline">Email</a>
                            @endif
                            @can('crm.contacts.delete')
                                <form action="{{ route('admin.crm.contacts.destroy', $contact) }}" method="POST" onsubmit="return confirm('Delete?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                </form>
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
