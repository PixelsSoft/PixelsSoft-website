@extends('admin.layout')

@section('title', 'Notifications')

@section('content')
<div class="card">
    <div class="card-header">
        <h2>Notifications</h2>
        <form method="POST" action="{{ route('admin.notifications.read-all') }}">
            @csrf
            <button type="submit" class="btn btn-sm btn-outline">Mark all read</button>
        </form>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>When</th><th>Message</th><th></th></tr></thead>
            <tbody>
                @forelse($notifications as $notification)
                    <tr class="{{ $notification->read_at ? '' : 'row-unread' }}">
                        <td>{{ $notification->created_at->format('M d, Y H:i') }}</td>
                        <td>
                            <strong>{{ $notification->data['title'] ?? 'Update' }}</strong>
                            <div class="form-meta">{{ $notification->data['body'] ?? '' }}</div>
                        </td>
                        <td>
                            <a href="{{ route('admin.notifications.read', $notification) }}" class="btn btn-sm btn-primary">Open</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="empty-state">No notifications yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $notifications->links() }}
</div>
@endsection
