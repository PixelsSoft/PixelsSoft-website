@extends('admin.layout')

@section('title', 'Dashboard')

@section('content')
<div class="connection-bar">
    <div class="connection-item">
        <span class="connection-dot online"></span>
        <div>
            <strong>API Server</strong>
            <span>{{ $apiUrl }}</span>
        </div>
    </div>
    <div class="connection-item">
        <span class="connection-dot online"></span>
        <div>
            <strong>Website</strong>
            <span>{{ $frontendUrl }}</span>
        </div>
    </div>
    <div class="connection-item">
        <span class="connection-dot {{ $stats['unread_messages'] > 0 ? 'online' : 'offline' }}"></span>
        <div>
            <strong>Contact Inbox</strong>
            <span>{{ $stats['unread_messages'] }} unread message{{ $stats['unread_messages'] !== 1 ? 's' : '' }}</span>
        </div>
    </div>
</div>

<div class="quick-actions">
    <a href="{{ route('admin.blogs.create') }}" class="quick-action">
        <div class="qa-icon">+</div>
        <div><strong>New Blog</strong><br><small style="color:#6b7280">Write a post</small></div>
    </a>
    <a href="{{ route('admin.portfolios.create') }}" class="quick-action">
        <div class="qa-icon">+</div>
        <div><strong>Add Portfolio</strong><br><small style="color:#6b7280">Showcase work</small></div>
    </a>
    <a href="{{ route('admin.media.index') }}" class="quick-action">
        <div class="qa-icon">&#128247;</div>
        <div><strong>Upload Media</strong><br><small style="color:#6b7280">Images & assets</small></div>
    </a>
    <a href="{{ $frontendUrl }}" target="_blank" class="quick-action">
        <div class="qa-icon">&#8599;</div>
        <div><strong>View Site</strong><br><small style="color:#6b7280">Open website</small></div>
    </a>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-label">Blogs</div>
        <div class="stat-value">{{ $stats['blogs'] }}</div>
        <div class="stat-icon">
            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2"/></svg>
        </div>
        <small style="color:#6b7280;margin-top:4px;display:block;">{{ $stats['published_blogs'] }} published</small>
    </div>
    <div class="stat-card">
        <div class="stat-label">Portfolio</div>
        <div class="stat-value">{{ $stats['portfolios'] }}</div>
        <div class="stat-icon">
            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14"/></svg>
        </div>
        <small style="color:#6b7280;margin-top:4px;display:block;">{{ $stats['published_portfolios'] }} published</small>
    </div>
    <div class="stat-card">
        <div class="stat-label">Showcase</div>
        <div class="stat-value">{{ $stats['showcases'] }}</div>
        <div class="stat-icon">
            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4v16M17 4v16M3 8h4m10 0h4"/></svg>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Services</div>
        <div class="stat-value">{{ $stats['services'] }}</div>
        <div class="stat-icon">
            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2>Recent Contact Messages</h2>
        <a href="{{ route('admin.messages.index') }}" class="btn btn-sm btn-outline">View All</a>
    </div>
    @if($recentMessages->count())
        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>Name</th><th>Email</th><th>Subject</th><th>Date</th><th>Status</th></tr>
                </thead>
                <tbody>
                    @foreach($recentMessages as $message)
                        <tr class="{{ $message->is_read ? '' : 'message-unread' }}">
                            <td>{{ $message->name }}</td>
                            <td>{{ $message->email }}</td>
                            <td>{{ $message->subject }}</td>
                            <td>{{ $message->created_at->format('M d, Y') }}</td>
                            <td>
                                @if($message->is_read)
                                    <span class="badge badge-published">Read</span>
                                @else
                                    <span class="badge badge-unread">New</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="empty-state">
            <h3>No messages yet</h3>
            <p>Contact form submissions from your website will appear here.</p>
        </div>
    @endif
</div>

<div class="card">
    <div class="card-header">
        <h2>Website Connection</h2>
    </div>
    <p style="color:#6b7280;font-size:14px;margin-bottom:12px;">
        Your Next.js website fetches content from this admin panel's API. Make sure these environment variables are set:
    </p>
    <div style="background:#f8fafc;border:1px solid #e5e7eb;border-radius:8px;padding:16px;font-family:monospace;font-size:13px;">
        <div style="margin-bottom:8px;"><strong style="color:#75dab4;">NEXT_PUBLIC_API_URL</strong>=<span style="color:#3b82f6;">{{ $apiUrl }}</span></div>
        <div><strong style="color:#75dab4;">NEXT_PUBLIC_SITE_URL</strong>=<span style="color:#3b82f6;">{{ $frontendUrl }}</span></div>
    </div>
</div>
@endsection
