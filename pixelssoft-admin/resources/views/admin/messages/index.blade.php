@extends('admin.layout')

@section('title', 'Contact Inbox')

@section('content')

<div class="card">

    <div class="card-header"><h2>Contact Messages</h2></div>

    <div class="table-wrap">

        <table>

            <thead><tr><th>Name</th><th>Email</th><th>Subject</th><th>Message</th><th>Date</th><th>Status</th></tr></thead>

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

                            <form action="{{ route('admin.messages.read', $message) }}" method="POST">@csrf @method('PATCH')<button class="btn btn-sm btn-accent">Mark Read</button></form>

                        @endif

                    </td>

                </tr>

            @empty

                <tr><td colspan="6"><div class="empty-state"><h3>No messages yet</h3><p>Submissions from the website contact form appear here.</p></div></td></tr>

            @endforelse

            </tbody>

        </table>

    </div>

    {{ $messages->links() }}

</div>

@endsection

