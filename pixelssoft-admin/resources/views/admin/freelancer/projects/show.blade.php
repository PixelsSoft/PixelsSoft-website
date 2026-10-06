@extends('admin.layout')

@section('title', $project->title)

@section('content')
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-error">{{ session('error') }}</div>@endif
@if(session('simulation'))
    @php $sim = session('simulation'); @endphp
    <div class="card" style="padding:1rem;margin-bottom:1rem;background:rgba(0,0,0,.02);">
        <h2 style="margin-top:0;">Bid Simulation</h2>
        <p><strong>Status:</strong> {{ $sim['ready'] ? 'READY TO BID' : 'BLOCKED' }}</p>
        <p><strong>Reason:</strong> {{ $sim['final_reason'] }}</p>
        <ul>
            @foreach($sim['checks'] as $name => $check)
                <li>{{ strtoupper($name) }}: {{ $check['ok'] ? 'OK' : 'BLOCKED' }} - {{ $check['detail'] }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="page-header" style="margin-bottom:1rem;">
    <a href="{{ route('admin.freelancer.projects.index') }}" class="btn btn-sm btn-outline">? Back</a>
    <h1 style="margin:.5rem 0;">{{ $project->title }}</h1>
    <p class="text-muted">
        {{ strtoupper($project->automation_status ?: 'new') }} · Match {{ $project->match_score }}%
        @if($project->project_url) · <a href="{{ $project->project_url }}" target="_blank" rel="noopener">Open on Freelancer</a>@endif
    </p>
</div>

<div class="stats-grid">
    <div class="stat-card"><div class="stat-label">Budget</div><div class="stat-value" style="font-size:1.05rem;">{{ $project->currency }} {{ $project->budget_min }}–{{ $project->budget_max }}</div></div>
    <div class="stat-card"><div class="stat-label">Client</div><div class="stat-value" style="font-size:1.05rem;">{{ $project->client_username ?: '—' }}</div></div>
    <div class="stat-card"><div class="stat-label">Type</div><div class="stat-value" style="font-size:1.05rem;">{{ $project->project_type ?: '—' }}</div></div>
    <div class="stat-card"><div class="stat-label">Bid Status</div><div class="stat-value" style="font-size:1.05rem;">{{ strtoupper($project->bid_status ?: 'pending') }}</div></div>
    <div class="stat-card"><div class="stat-label">Target</div><div class="stat-value" style="font-size:1.05rem;">{{ $project->target_bid_seconds ?: 60 }} sec</div></div>
    <div class="stat-card"><div class="stat-label">Actual Time To Bid</div><div class="stat-value" style="font-size:1.05rem;">{{ $project->total_time_to_bid_ms ? round($project->total_time_to_bid_ms / 1000, 2) . ' sec' : '—' }}</div></div>
</div>

<div class="card" style="margin-bottom:1rem;">
    <div class="card-header"><h2>Description</h2></div>
    <div style="padding:1rem;white-space:pre-wrap;">{{ $project->description }}</div>
</div>

<div class="card" style="margin-bottom:1rem;">
    <div class="card-header"><h2>Qualification And Timing</h2></div>
    <div style="padding:1rem;display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:.75rem;">
        <div><strong>Detected At:</strong> {{ $project->detected_at ?: '—' }}</div>
        <div><strong>Matching Started:</strong> {{ $project->matching_started_at ?: '—' }}</div>
        <div><strong>Matching Completed:</strong> {{ $project->matching_completed_at ?: '—' }}</div>
        <div><strong>Portfolio Selected:</strong> {{ $project->portfolio_selected_at ?: '—' }}</div>
        <div><strong>AI Started:</strong> {{ $project->ai_started_at ?: '—' }}</div>
        <div><strong>AI Completed:</strong> {{ $project->ai_completed_at ?: '—' }}</div>
        <div><strong>Bid Queued:</strong> {{ $project->bid_queued_at ?: '—' }}</div>
        <div><strong>Bid Started:</strong> {{ $project->bid_started_at ?: '—' }}</div>
        <div><strong>Bid Submitted:</strong> {{ $project->bid_submitted_at ?: '—' }}</div>
        <div><strong>Total Processing:</strong> {{ $project->total_processing_time_ms ? round($project->total_processing_time_ms / 1000, 2) . ' sec' : '—' }}</div>
        <div><strong>Rejected Reason:</strong> {{ $project->reject_reason ?: '—' }}</div>
        <div><strong>Strategy:</strong> {{ $project->strategy?->name ?: '—' }}</div>
    </div>
</div>

<div class="card" style="margin-bottom:1rem;">
    <div class="card-header"><h2>Match Explanation</h2></div>
    <div style="padding:1rem;">
        <ul>
            @foreach(($project->match_explanation['parts'] ?? []) as $part)
                <li>{{ ($part['ok'] ?? false) ? 'OK' : 'BLOCKED' }} - {{ $part['label'] ?? '' }}: {{ $part['detail'] ?? '' }}</li>
            @endforeach
        </ul>
    </div>
</div>

<div class="card" style="margin-bottom:1rem;">
    <div class="card-header"><h2>Selected Portfolio Links</h2></div>
    <div style="padding:1rem;">
        @forelse($selectedPortfolioLinks as $link)
            <div style="padding:.75rem 0;border-bottom:1px solid rgba(0,0,0,.06);">
                <div><strong>{{ $link->title }}</strong> <a href="{{ $link->url }}" target="_blank" rel="noopener">{{ $link->url }}</a></div>
                <div class="text-muted">Skills: {{ $link->skills->pluck('name')->implode(', ') ?: '—' }}</div>
            </div>
        @empty
            <p class="text-muted">No CRM-owned portfolio links selected for this project yet.</p>
        @endforelse
    </div>
</div>

<div class="card" style="margin-bottom:1rem;">
    <div class="card-header"><h2>Suggested Bid</h2></div>
    <div class="card-body">
    <form method="post" action="{{ route('admin.freelancer.projects.suggestion', $project) }}" class="admin-form admin-form-medium">
        @csrf
        @method('PUT')
        <div class="form-row">
            <label>Amount<input type="number" step="0.01" name="amount" value="{{ old('amount', $project->suggested_amount) }}" class="form-control"></label>
            <label>Delivery days<input type="number" name="delivery_days" value="{{ old('delivery_days', $project->suggested_delivery_days) }}" class="form-control"></label>
        </div>
        <label>Proposal<textarea name="proposal" rows="10" class="form-control">{{ old('proposal', $project->suggested_proposal) }}</textarea></label>
        <label>Portfolio links
            <select class="form-control" name="portfolio_ids[]" multiple size="6">
                @foreach($project->account->portfolioLinks as $link)
                    <option value="{{ $link->id }}" @selected(in_array($link->id, $project->selected_portfolio_ids))>{{ $link->title }}</option>
                @endforeach
            </select>
        </label>
        <div class="form-actions">
            <button class="btn btn-outline" type="submit">Save Changes</button>
            @can('freelancer.projects.manage')
            <button formaction="{{ route('admin.freelancer.projects.prepare', $project) }}" formmethod="post" class="btn btn-outline" type="submit">Prepare</button>
            <button formaction="{{ route('admin.freelancer.projects.regenerate', $project) }}" formmethod="post" class="btn btn-outline" type="submit">Regenerate</button>
            <button formaction="{{ route('admin.freelancer.projects.simulate', $project) }}" formmethod="post" class="btn btn-outline" type="submit">Simulate Bid</button>
            <button formaction="{{ route('admin.freelancer.projects.approve', $project) }}" formmethod="post" class="btn btn-primary" type="submit">Approve & Bid</button>
            <button formaction="{{ route('admin.freelancer.projects.bid', $project) }}" formmethod="post" class="btn btn-outline" type="submit">Bid Now</button>
            <button formaction="{{ route('admin.freelancer.projects.reject', $project) }}" formmethod="post" class="btn btn-outline" type="submit">Reject</button>
            <button formaction="{{ route('admin.freelancer.projects.ignore', $project) }}" formmethod="post" class="btn btn-outline" type="submit">Ignore</button>
            @endcan
        </div>
    </form>
    </div>
</div>

<div class="card" style="margin-bottom:1rem;">
    <div class="card-header"><h2>Automation Log</h2></div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Time</th><th>Stage</th><th>Level</th><th>Message</th><th>Context</th></tr></thead>
            <tbody>
                @forelse($project->automationLogs->sortByDesc('occurred_at') as $log)
                    <tr>
                        <td>{{ $log->occurred_at ?: $log->created_at }}</td>
                        <td>{{ $log->stage }}</td>
                        <td>{{ strtoupper($log->level) }}</td>
                        <td>{{ $log->message }}</td>
                        <td><pre style="white-space:pre-wrap;">{{ json_encode($log->context, JSON_PRETTY_PRINT) }}</pre></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="empty-state">No automation logs yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <div class="card-header"><h2>Bids</h2></div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Status</th><th>Amount</th><th>Submitted</th><th>AI</th><th>Message</th></tr></thead>
            <tbody>
                @forelse($project->bids as $bid)
                    <tr>
                        <td>{{ strtoupper($bid->status) }}</td>
                        <td>{{ $bid->currency }} {{ $bid->amount }}</td>
                        <td>{{ $bid->submitted_at ?: '—' }}</td>
                        <td>{{ $bid->ai_provider ?: 'Template' }} {{ $bid->ai_model ?: '' }}</td>
                        <td>{{ $bid->error_message ?: '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="empty-state">No bids for this project.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
