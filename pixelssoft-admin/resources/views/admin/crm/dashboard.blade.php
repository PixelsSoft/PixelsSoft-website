@extends('admin.layout')

@section('title', 'CRM Dashboard')

@section('content')
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-label">Total Leads</div>
        <div class="stat-value">{{ $stats['leads'] }}</div>
        <small style="color:#6b7280">{{ $stats['new_leads'] }} new</small>
    </div>
    <div class="stat-card">
        <div class="stat-label">Open Deals</div>
        <div class="stat-value">{{ $stats['open_deals'] }}</div>
        <small style="color:#6b7280">{{ $stats['deals'] }} total</small>
    </div>
    <div class="stat-card">
        <div class="stat-label">Pipeline Value</div>
        <div class="stat-value">${{ number_format($stats['pipeline_value'], 0) }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Won Deals</div>
        <div class="stat-value">{{ $stats['won_deals'] }}</div>
    </div>
</div>

<div class="quick-actions">
    @can('crm.leads.create')
        <a href="{{ route('admin.crm.leads.create') }}" class="quick-action"><div class="qa-icon">+</div><div><strong>New Lead</strong></div></a>
    @endcan
    @can('crm.deals.create')
        <a href="{{ route('admin.crm.deals.create') }}" class="quick-action"><div class="qa-icon">+</div><div><strong>New Deal</strong></div></a>
    @endcan
    <a href="{{ route('admin.crm.deals.kanban') }}" class="quick-action"><div class="qa-icon">▦</div><div><strong>Pipeline Board</strong></div></a>
</div>

<div class="card">
    <div class="card-header">
        <h2>Recent Leads</h2>
        <a href="{{ route('admin.crm.leads.index') }}" class="btn btn-sm btn-outline">View All</a>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Title</th><th>Status</th><th>Score</th><th>Owner</th><th>Date</th></tr></thead>
            <tbody>
                @forelse($recentLeads as $lead)
                    <tr>
                        <td><a href="{{ route('admin.crm.leads.show', $lead) }}">{{ $lead->title }}</a></td>
                        <td><span class="badge badge-draft">{{ $lead->status }}</span></td>
                        <td><span class="badge score-{{ $lead->score }}">{{ $lead->score }}</span></td>
                        <td>{{ $lead->owner?->name ?? '—' }}</td>
                        <td>{{ $lead->created_at->format('M d, Y') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="empty-state">No leads yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
