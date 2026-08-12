@extends('admin.layout')

@section('title', 'Contact Inbox')

@section('content')
<div class="card">
    <div class="card-header">
        <h2>Contact Messages</h2>
    </div>

    @include('admin.partials.list-toolbar', [
        'showSearch' => true,
        'searchPlaceholder' => 'Search name, email, subject…',
        'filters' => [
            [
                'name' => 'status',
                'label' => 'All statuses',
                'options' => [
                    'unread' => 'Unread',
                    'read' => 'Read',
                ],
            ],
        ],
    ])

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Subject</th>
                    <th>Message</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse($messages as $message)
                <tr class="{{ $message->is_read ? '' : 'message-unread' }}">
                    <td><strong>{{ $message->name }}</strong></td>
                    <td><a href="mailto:{{ $message->email }}">{{ $message->email }}</a></td>
                    <td>{{ $message->subject ?? '—' }}</td>
                    <td><div class="message-preview" title="{{ $message->message }}">{{ $message->message }}</div></td>
                    <td>{{ $message->created_at->format('M d, Y H:i') }}</td>
                    <td>
                        @if($message->is_read)
                            <span class="badge badge-published">Read</span>
                        @else
                            <span class="badge badge-unread">New</span>
                        @endif
                    </td>
                    <td class="table-actions">
                        @if(!$message->is_read)
                            <form action="{{ route('admin.messages.read', $message) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-sm btn-accent">Mark Read</button>
                            </form>
                        @endif
                        <a href="mailto:{{ $message->email }}?subject=Re: {{ urlencode($message->subject ?? 'Your inquiry') }}" class="btn btn-sm btn-outline">Reply</a>
                        <form action="{{ route('admin.messages.destroy', $message) }}" method="POST" onsubmit="return confirm('Delete this message?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">
                        <div class="empty-state">
                            <h3>No messages yet</h3>
                            <p>Submissions from the website contact form appear here.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{ $messages->links() }}
</div>
@endsection
