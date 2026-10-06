@extends('admin.layout')

@section('title', 'Freelancer Account')

@section('content')
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-error">{{ session('error') }}</div>@endif

<div class="page-header" style="margin-bottom:1rem;">
    <h1 style="margin:0;">Freelancer Account</h1>
    <p class="text-muted">Connect via official Freelancer OAuth. Tokens are encrypted and never exposed in the UI.</p>
</div>

@if(!$configured)
<div class="alert alert-error">
    Freelancer OAuth is not configured. Set <code>FREELANCER_CLIENT_ID</code>, <code>FREELANCER_CLIENT_SECRET</code>, and <code>FREELANCER_REDIRECT_URI</code> in <code>.env</code>.
</div>
@endif

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-label">Connection</div>
        <div class="stat-value" style="font-size:1.1rem;">{{ $account->tokenStatusLabel() }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Username</div>
        <div class="stat-value" style="font-size:1.1rem;">{{ $account->username ?: '—' }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Last Sync</div>
        <div class="stat-value" style="font-size:1.1rem;">{{ $account->last_sync_at?->diffForHumans() ?: 'Never' }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Token Expiry</div>
        <div class="stat-value" style="font-size:1.1rem;">{{ $account->token_expires_at?->toDayDateTimeString() ?: 'n/a' }}</div>
    </div>
</div>

<div class="card" style="margin-bottom:1rem;">
    <div class="card-header"><h2>Profile</h2></div>
    <div style="padding:1rem;display:grid;gap:.5rem;">
        <div><strong>Freelancer ID:</strong> {{ $account->freelancer_user_id ?: '—' }}</div>
        <div><strong>Display name:</strong> {{ $account->display_name ?: '—' }}</div>
        <div><strong>Location:</strong> {{ trim(($account->city ? $account->city.', ' : '').($account->country ?: '')) ?: '—' }}</div>
        <div><strong>Hourly rate:</strong> {{ $account->hourly_rate ?: '—' }}</div>
        <div><strong>Rating:</strong> {{ $account->rating ?: '—' }} ({{ $account->reviews_count }} reviews)</div>
        <div><strong>Profile:</strong> @if($account->profile_url)<a href="{{ $account->profile_url }}" target="_blank" rel="noopener">{{ $account->profile_url }}</a>@else — @endif</div>
        <div><strong>Environment:</strong> {{ config('freelancer.sandbox') ? 'Sandbox' : 'Production' }}</div>
        @if($account->last_api_error)
            <div><strong>Last API error:</strong> {{ $account->last_api_error }}</div>
        @endif
    </div>
    <div style="padding:0 1rem 1rem;display:flex;gap:.5rem;flex-wrap:wrap;">
        @if(!$account->is_connected)
            <a href="{{ route('admin.freelancer.oauth.redirect') }}" class="btn btn-primary">Connect Freelancer Account</a>
        @else
            <a href="{{ route('admin.freelancer.oauth.redirect') }}" class="btn btn-outline">Reconnect</a>
            <form method="post" action="{{ route('admin.freelancer.account.test') }}">@csrf<button class="btn btn-outline" type="submit">Test Connection</button></form>
            <form method="post" action="{{ route('admin.freelancer.account.sync') }}">@csrf<button class="btn btn-outline" type="submit">Sync Profile</button></form>
            <form method="post" action="{{ route('admin.freelancer.account.disconnect') }}" onsubmit="return confirm('Disconnect Freelancer account?')">@csrf<button class="btn btn-danger" type="submit">Disconnect</button></form>
        @endif
    </div>
</div>

<div class="card">
    <div class="card-header"><h2>Recent API Health</h2></div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Time</th><th>Method</th><th>Endpoint</th><th>Status</th><th>ms</th><th>Result</th></tr></thead>
            <tbody>
                @forelse($recentLogs as $log)
                    <tr>
                        <td>{{ $log->created_at }}</td>
                        <td>{{ $log->method }}</td>
                        <td>{{ $log->endpoint }}</td>
                        <td>{{ $log->status_code }}</td>
                        <td>{{ $log->response_time_ms }}</td>
                        <td>{{ $log->success ? 'OK' : ($log->error_message ?: 'Failed') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="empty-state">No API calls yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
