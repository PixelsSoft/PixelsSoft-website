@extends('admin.layout')

@section('title', 'Freelancer Analytics')

@section('content')
<div class="page-header" style="margin-bottom:1rem;">
    <h1 style="margin:0;">Analytics</h1>
    <form method="get" style="display:flex;gap:.5rem;flex-wrap:wrap;margin-top:.75rem;">
        @foreach(['today'=>'Today','yesterday'=>'Yesterday','7d'=>'Last 7 Days','30d'=>'Last 30 Days','month'=>'This Month','last_month'=>'Last Month'] as $key=>$label)
            <a href="?range={{ $key }}" class="btn btn-sm {{ request('range', '7d') === $key ? 'btn-primary' : 'btn-outline' }}">{{ $label }}</a>
        @endforeach
    </form>
</div>

<div class="stats-grid">
    <div class="stat-card"><div class="stat-label">Discovered</div><div class="stat-value">{{ $metrics['discovered'] }}</div></div>
    <div class="stat-card"><div class="stat-label">Qualified</div><div class="stat-value">{{ $metrics['qualified'] }}</div></div>
    <div class="stat-card"><div class="stat-label">Submitted</div><div class="stat-value">{{ $metrics['submitted'] }}</div></div>
    <div class="stat-card"><div class="stat-label">Win Rate</div><div class="stat-value">{{ $metrics['win_rate'] }}%</div></div>
    <div class="stat-card"><div class="stat-label">Within Target</div><div class="stat-value">{{ $metrics['within_target_percent'] }}%</div></div>
    <div class="stat-card"><div class="stat-label">Avg Time To Bid</div><div class="stat-value">{{ $metrics['avg_time_to_bid_ms'] ? round($metrics['avg_time_to_bid_ms']/1000, 2) . ' sec' : '—' }}</div></div>
    <div class="stat-card"><div class="stat-label">Avg AI Time</div><div class="stat-value">{{ $metrics['avg_ai_time_ms'] ? round($metrics['avg_ai_time_ms']/1000, 2) . ' sec' : '—' }}</div></div>
    <div class="stat-card"><div class="stat-label">Avg API Time</div><div class="stat-value">{{ $metrics['avg_api_time_ms'] ? round($metrics['avg_api_time_ms']/1000, 2) . ' sec' : '—' }}</div></div>
</div>

<div class="card" style="margin-bottom:1rem;"><div class="card-header"><h2>Timing Funnel</h2></div><div style="padding:1rem;">Discovered ({{ $metrics['discovered'] }}) ? Qualified ({{ $metrics['qualified'] }}) ? Submitted ({{ $metrics['submitted'] }}) ? Accepted ({{ $metrics['accepted'] }})<p class="text-muted">One-minute targets depend on scheduler cadence, queue workers, official Freelancer API latency, and optional AI provider latency.</p></div></div>

<div class="card" style="margin-bottom:1rem;"><div class="card-header"><h2>Speed Metrics</h2></div><div style="padding:1rem;display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:.75rem;"><div><strong>Bids Within Target:</strong> {{ $metrics['bids_within_target'] }}</div><div><strong>Bids Over Target:</strong> {{ $metrics['bids_over_target'] }}</div><div><strong>Fastest Bid:</strong> {{ $metrics['fastest_bid_ms'] ? round($metrics['fastest_bid_ms']/1000, 2) . ' sec' : '—' }}</div><div><strong>Slowest Bid:</strong> {{ $metrics['slowest_bid_ms'] ? round($metrics['slowest_bid_ms']/1000, 2) . ' sec' : '—' }}</div><div><strong>Avg Queue Time:</strong> {{ $metrics['avg_queue_time_ms'] ? round($metrics['avg_queue_time_ms']/1000, 2) . ' sec' : '—' }}</div></div></div>

<div class="card" style="margin-bottom:1rem;"><div class="card-header"><h2>By AI Provider</h2></div><div class="table-wrap"><table><thead><tr><th>Provider</th><th>Model</th><th>Runs</th></tr></thead><tbody>@forelse($byAi as $row)<tr><td>{{ $row->ai_provider ?: 'Template' }}</td><td>{{ $row->ai_model ?: '—' }}</td><td>{{ $row->total }}</td></tr>@empty<tr><td colspan="3" class="empty-state">No AI usage yet.</td></tr>@endforelse</tbody></table></div></div>

<div class="card" style="margin-bottom:1rem;"><div class="card-header"><h2>Portfolio Usage</h2></div><div class="table-wrap"><table><thead><tr><th>Title</th><th>Skills</th><th>Usage</th><th>Last Used</th></tr></thead><tbody>@forelse($portfolioUsage as $row)<tr><td>{{ $row->title }}</td><td>{{ $row->skills->pluck('name')->implode(', ') ?: '—' }}</td><td>{{ $row->usage_count }}</td><td>{{ $row->last_used_at ?: '—' }}</td></tr>@empty<tr><td colspan="4" class="empty-state">No portfolio usage yet.</td></tr>@endforelse</tbody></table></div></div>

<div class="card" style="margin-bottom:1rem;"><div class="card-header"><h2>By Country</h2></div><div class="table-wrap"><table><thead><tr><th>Country</th><th>Projects</th><th>Qualified</th></tr></thead><tbody>@forelse($byCountry as $row)<tr><td>{{ $row->country ?: 'Unknown' }}</td><td>{{ $row->projects }}</td><td>{{ $row->qualified }}</td></tr>@empty<tr><td colspan="3" class="empty-state">No data.</td></tr>@endforelse</tbody></table></div></div>

<div class="card" style="margin-bottom:1rem;"><div class="card-header"><h2>By Strategy</h2></div><div class="table-wrap"><table><thead><tr><th>Strategy</th><th>Bids</th><th>Wins</th><th>Win Rate</th><th>Avg Bid</th><th>Avg Match</th></tr></thead><tbody>@forelse($byStrategy as $row)<tr><td>{{ $row['name'] }}</td><td>{{ $row['bids'] }}</td><td>{{ $row['wins'] }}</td><td>{{ $row['win_rate'] }}%</td><td>{{ $row['avg_bid'] }}</td><td>{{ $row['avg_match'] }}%</td></tr>@empty<tr><td colspan="6" class="empty-state">No strategy data.</td></tr>@endforelse</tbody></table></div></div>

<div class="card"><div class="card-header"><h2>Failed Bid Reasons</h2></div><div class="table-wrap"><table><thead><tr><th>Reason</th><th>Count</th></tr></thead><tbody>@forelse($failures as $failure)<tr><td>{{ $failure->error_message ?: 'Unknown' }}</td><td>{{ $failure->total }}</td></tr>@empty<tr><td colspan="2" class="empty-state">No failures.</td></tr>@endforelse</tbody></table></div></div>
@endsection
