@extends('admin.layout')

@section('title', 'Users')

@section('content')
<div class="card">
    <div class="card-header">
        <h2>Users</h2>
        @can('system.users.create')
            <a href="{{ route('admin.system.users.create') }}" class="btn btn-primary">+ New User</a>
        @endcan
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr><th>Name</th><th>Email</th><th>Roles</th><th>Status</th><th>Last Login</th><th>Actions</th></tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr>
                        <td><strong>{{ $user->name }}</strong></td>
                        <td>{{ $user->email }}</td>
                        <td>
                            @foreach($user->roles as $role)
                                <span class="badge badge-draft">{{ $role->name }}</span>
                            @endforeach
                        </td>
                        <td>
                            <span class="badge {{ $user->status === 'active' ? 'badge-published' : 'badge-unread' }}">{{ $user->status }}</span>
                        </td>
                        <td>{{ $user->last_login_at?->format('M d, Y H:i') ?? '—' }}</td>
                        <td class="actions">
                            @can('system.users.edit')
                                <a href="{{ route('admin.system.users.edit', $user) }}" class="btn btn-sm btn-outline">Edit</a>
                            @endcan
                            @can('system.users.delete')
                                @if($user->id !== auth()->id())
                                    <form action="{{ route('admin.system.users.destroy', $user) }}" method="POST" style="display:inline" onsubmit="return confirm('Delete this user?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                    </form>
                                @endif
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="empty-state">No users found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $users->links() }}
</div>
@endsection
