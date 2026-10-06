@extends('admin.layout')

@section('title', 'Freelancer Dashboard')

@section('content')
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-error">{{ session('error') }}</div>@endif

<div class="page-header" style="display:flex;justify-content:space-between;align-items:center;gap:1rem;flex-wrap:wrap;margin-bottom:1rem;">
    <div>
        <h1 style="margin:0;">Freelancer Dashboard</h1>
        <p class="text-muted" style="margin:.25rem 0 0;">
            Automation:
            @if(!$account)
                Not configured
            @elseif($account->global_paused)
                <span class="badge badge-draft">PAUSED</span>
            @elseif($account->automation_enabled)
                <span class="badge badge-published">ACTIVE</span>
            @else
                <span class="badge badge-draft">OFF</span>
            @endif
            @if($account?->dry_run)
                · <span class="badge badge-draft">DRY RUN</span>
            @endif
        </p>
    </div>
    <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
        @can('freelancer.account.manage')
            <a href="{{ route('admin.freelancer.account.index') }}" class="btn btn-outline btn-sm">Account</a>
        @endcan
        @can('freelancer.automation.manage')
            <a href="{{ route('admin.freelancer.automation.index') }}" class="btn btn-primary btn-sm">Automation</a>
        @endcan
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card"><div class="stat-label">Projects Found Today</div><div class="stat-value">{{ $stats['projects_today'] }}</div></div>
    <div class="stat-card"><div class="stat-label">Qualified</div><div class="stat-value">{{ $stats['qualified'] }}</div></div>
    <div class="stat-card"><div class="stat-label">Rejected</div><div class="stat-value">{{ $stats['rejected'] }}</div></div>
    <div class="stat-card"><div class="stat-label">Bids Today</div><div class="stat-value">{{ $stats['bids_today'] }}</div></div>
    <div class="stat-card"><div class="stat-label">Bids This Week</div><div class="stat-value">{{ $stats['bids_week'] }}</div></div>
    <div class="stat-card"><div class="stat-label">Bids This Month</div><div class="stat-value">{{ $stats['bids_month'] }}</div></div>
    <div class="stat-card"><div class="stat-label">Successful</div><div class="stat-value">{{ $stats['successful'] }}</div></div>
    <div class="stat-card"><div class="stat-label">Failed</div><div class="stat-value">{{ $stats['failed'] }}</div></div>
    <div class="stat-card"><div class="stat-label">Pending</div><div class="stat-value">{{ $stats['pending'] }}</div></div>
    <div class="stat-card"><div class="stat-label">Win Rate</div><div class="stat-value">{{ $stats['win_rate'] }}%</div></div>
    <div class="stat-card"><div class="stat-label">Avg Bid</div><div class="stat-value">{{ $stats['avg_bid'] }}</div></div>
    <div class="stat-card"><div class="stat-label">Avg Match</div><div class="stat-value">{{ $stats['avg_match'] }}%</div></div>
</div>

<div class="card">
    <div class="card-header">
        <h2>Recent Projects</h2>
        <a href="{{ route('admin.freelancer.projects.index') }}" class="btn btn-sm btn-outline">View All</a>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Project</th>
                    <th>Budget</th>
                    <th>Country</th>
                    <th>Match</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($recent as $project)
                    <tr>
                        <td>{{ Str::limit($project->title, 60) }}</td>
                        <td>{{ $project->currency }} {{ $project->budget_min }}@if($project->budget_max && $project->budget_max != $project->budget_min)–{{ $project->budget_max }}@endif</td>
                        <td>{{ $project->country ?: '—' }}</td>
                        <td>{{ $project->match_score }}%</td>
                        <td><span class="badge badge-draft">{{ $project->automation_status }}</span></td>
                        <td><a href="{{ route('admin.freelancer.projects.show', $project) }}" class="btn btn-sm btn-outline">View</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="empty-state">No projects yet. Connect your account and sync.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
