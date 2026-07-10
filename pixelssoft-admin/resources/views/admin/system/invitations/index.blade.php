@extends('admin.layout')

@section('title', 'Invitations')

@section('content')
<div class="card" style="margin-bottom:24px">
    <div class="card-header"><h2>Invite New User</h2></div>
    <form method="POST" action="{{ route('admin.system.invitations.store') }}">
        @csrf
        <div class="form-grid">
            <div class="form-group">
                <label>Email *</label>
                <input type="email" name="email" value="{{ old('email') }}" required>
            </div>
            <div class="form-group">
                <label>Name</label>
                <input type="text" name="name" value="{{ old('name') }}">
            </div>
        </div>
        <div class="form-group" style="margin-top:16px">
            <label>Roles *</label>
            <div class="permission-grid">
                @foreach($roles as $role)
                    <label class="permission-check">
                        <input type="checkbox" name="roles[]" value="{{ $role }}" @checked(in_array($role, old('roles', [])))>
                        {{ str_replace('-', ' ', ucwords($role, '-')) }}
                    </label>
                @endforeach
            </div>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Create Invitation</button>
        </div>
    </form>
</div>

<div class="card">
    <div class="card-header"><h2>Pending Invitations</h2></div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr><th>Email</th><th>Roles</th><th>Invited By</th><th>Expires</th><th>Status</th><th>Actions</th></tr>
            </thead>
            <tbody>
                @forelse($invitations as $invitation)
                    <tr>
                        <td>{{ $invitation->email }}</td>
                        <td>{{ implode(', ', $invitation->roles ?? []) }}</td>
                        <td>{{ $invitation->inviter?->name }}</td>
                        <td>{{ $invitation->expires_at->format('M d, Y') }}</td>
                        <td>
                            @if($invitation->isAccepted())
                                <span class="badge badge-published">Accepted</span>
                            @elseif($invitation->isExpired())
                                <span class="badge badge-unread">Expired</span>
                            @else
                                <span class="badge badge-draft">Pending</span>
                            @endif
                        </td>
                        <td>
                            @if(!$invitation->isAccepted())
                                <code style="font-size:11px">{{ route('invite.accept', $invitation->token) }}</code>
                                <form action="{{ route('admin.system.invitations.destroy', $invitation) }}" method="POST" style="display:inline;margin-left:8px" onsubmit="return confirm('Revoke invitation?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Revoke</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="empty-state">No invitations yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $invitations->links() }}
</div>
@endsection
