@extends('admin.layout')

@section('title', $deal->title)

@section('content')
@php
    $project = $deal->project;
    $currency = $project?->currency ?: ($deal->currency ?: 'USD');
    $finance = $project ? $project->financeSummary() : null;
    $portalUrl = $deal->portal_url ?: $deal->lead?->portal_url;
@endphp

<div data-ui-tabs>
    <div class="page-header">
        <div class="page-header-main">
            <div class="page-kicker">Deal</div>
            <h1>{{ $deal->title }}</h1>
            <div class="page-header-meta">
                <span class="badge badge-draft">{{ $deal->stage?->name ?? 'No stage' }}</span>
                <span class="form-meta">{{ $currency }} {{ number_format((float) $deal->value, 2) }}</span>
            </div>
        </div>
        <div class="page-header-actions">
            <a href="{{ route('admin.crm.deals.kanban') }}" class="btn btn-sm btn-outline">Pipeline</a>
            @can('crm.deals.edit')
                <a href="{{ route('admin.crm.deals.edit', $deal) }}" class="btn btn-sm btn-outline">Edit</a>
            @endcan
        </div>
    </div>

    @if($project && $finance)
        <div class="stats-grid stats-grid-sm">
            <div class="stat-card"><div class="stat-label">Contract</div><div class="stat-value">{{ $currency }} {{ number_format($finance['contract'], 0) }}</div></div>
            <div class="stat-card"><div class="stat-label">Released</div><div class="stat-value">{{ $currency }} {{ number_format($finance['released_gross'], 0) }}</div></div>
            <div class="stat-card"><div class="stat-label">Net received</div><div class="stat-value">{{ $currency }} {{ number_format($finance['net_received'], 0) }}</div></div>
            <div class="stat-card"><div class="stat-label">Remaining</div><div class="stat-value">{{ $currency }} {{ number_format($finance['remaining'], 0) }}</div></div>
        </div>
    @endif

    <div class="ui-tabs" role="tablist">
        <button type="button" class="ui-tab is-active" data-tab="overview" role="tab">Overview</button>
        @if($project)
            <button type="button" class="ui-tab" data-tab="finance" role="tab">Finance</button>
            <button type="button" class="ui-tab" data-tab="milestones" role="tab">Milestones ({{ $project->milestones->count() }})</button>
        @endif
    </div>

    <div class="ui-tab-panel is-active" data-panel="overview">
        <div class="card">
            <div class="card-header"><h2>Details</h2></div>
            <div class="dl-grid">
                <div><span class="dl-label">Stage</span><div class="dl-value">{{ $deal->stage?->name ?? '—' }}</div></div>
                <div><span class="dl-label">Company</span><div class="dl-value">{{ $deal->company?->name ?? '—' }}</div></div>
                <div><span class="dl-label">Source</span><div class="dl-value">{{ $deal->acquisitionSource?->name ?? '—' }}</div></div>
                <div><span class="dl-label">Value</span><div class="dl-value">{{ $currency }} {{ number_format((float) $deal->value, 2) }}</div></div>
                <div><span class="dl-label">Sales</span><div class="dl-value">{{ $deal->salesperson?->name ?? '—' }}</div></div>
                <div><span class="dl-label">Portal ID</span><div class="dl-value">{{ $deal->portal_contract_id ?: ($deal->lead?->portal_contract_id ?: '—') }}</div></div>
                <div>
                    <span class="dl-label">Portal URL</span>
                    <div class="dl-value">
                        @if($portalUrl)
                            <a href="{{ $portalUrl }}" target="_blank" rel="noopener">Open job</a>
                        @else
                            —
                        @endif
                    </div>
                </div>
                @if($project)
                    <div>
                        <span class="dl-label">Delivery project</span>
                        <div class="dl-value">
                            <a href="{{ route('admin.pm.projects.show', $project) }}">{{ $project->code }} · {{ $project->name }}</a>
                        </div>
                    </div>
                @endif
            </div>
            @if($deal->notes)
                <div style="margin-top:14px;padding-top:12px;border-top:1px solid var(--border)">
                    <span class="dl-label">Notes</span>
                    <p style="margin-top:6px;color:#6b7280;font-size:14px">{{ $deal->notes }}</p>
                </div>
            @endif
        </div>

        @unless($project)
            <div class="card">
                <p class="empty-state">Win this deal on the pipeline to create a delivery project. Then you can add and release milestones.</p>
            </div>
        @endunless
    </div>

    @if($project && $finance)
        <div class="ui-tab-panel" data-panel="finance">
            <div class="stats-grid stats-grid-sm">
                <div class="stat-card"><div class="stat-label">Contract</div><div class="stat-value">{{ $currency }} {{ number_format($finance['contract'], 0) }}</div></div>
                <div class="stat-card"><div class="stat-label">Released</div><div class="stat-value">{{ $currency }} {{ number_format($finance['released_gross'], 0) }}</div></div>
                <div class="stat-card"><div class="stat-label">Platform fees</div><div class="stat-value">{{ $currency }} {{ number_format($finance['platform_fees'], 0) }}</div></div>
                <div class="stat-card"><div class="stat-label">Net received</div><div class="stat-value">{{ $currency }} {{ number_format($finance['net_received'], 0) }}</div></div>
                <div class="stat-card"><div class="stat-label">Sales commission</div><div class="stat-value">{{ $currency }} {{ number_format($finance['sales_commissions'], 0) }}</div></div>
                <div class="stat-card"><div class="stat-label">Remaining</div><div class="stat-value">{{ $currency }} {{ number_format($finance['remaining'], 0) }}</div></div>
            </div>
        </div>

        <div class="ui-tab-panel" data-panel="milestones">
            <div class="table-card">
                <div class="card-header"><h2>Milestones</h2></div>
                <div class="page-help">
                    <p>Sales creates and releases here. Accounts records the wallet when money lands.</p>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Due</th>
                                <th class="text-right">Gross</th>
                                <th class="text-right">Platform</th>
                                <th class="text-right">Net</th>
                                <th class="text-right">Sales</th>
                                <th>Status</th>
                                <th>Released</th>
                                @canany(['pm.milestones.manage', 'pm.milestones.release'])<th></th>@endcanany
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($project->milestones as $milestone)
                                @php $split = $preview->split((float) $milestone->amount, $project); @endphp
                                <tr>
                                    <td><strong>{{ $milestone->title }}</strong></td>
                                    <td>{{ $milestone->due_date?->format('M d, Y') ?? '—' }}</td>
                                    <td class="text-right num">{{ $currency }} {{ number_format($milestone->amount, 2) }}</td>
                                    <td class="text-right num">
                                        {{ $currency }} {{ number_format($milestone->isReleased() ? $milestone->platform_fee_amount : $split['platform_fee'], 2) }}
                                    </td>
                                    <td class="text-right num">
                                        @if($milestone->isSettled())
                                            {{ $milestone->received_currency ?: $currency }} {{ number_format($milestone->received_amount ?: $milestone->net_amount, 2) }}
                                            <div class="form-meta">{{ $milestone->paymentAccount?->name }}</div>
                                        @else
                                            {{ $currency }} {{ number_format($milestone->isReleased() ? $milestone->net_amount : $split['net'], 2) }}
                                        @endif
                                    </td>
                                    <td class="text-right num">
                                        {{ $currency }} {{ number_format($milestone->isReleased() ? $milestone->sales_commission_amount : $split['sales_commission'], 2) }}
                                    </td>
                                    <td>
                                        <span class="badge {{ $milestone->isSettled() ? 'badge-published' : ($milestone->isAwaitingSettlement() ? 'badge-unread' : 'badge-draft') }}">
                                            {{ str_replace('_', ' ', $milestone->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($milestone->released_at)
                                            {{ $milestone->released_at->format('M d, Y') }}
                                            <div class="form-meta">{{ $milestone->releasedBy?->name }}</div>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    @canany(['pm.milestones.manage', 'pm.milestones.release'])
                                        <td class="actions">
                                            @can('pm.milestones.manage')
                                                @unless($milestone->isReleased())
                                                    <form action="{{ route('admin.crm.deals.milestones.destroy', [$deal, $milestone]) }}" method="POST" style="display:inline" onsubmit="return confirm('Remove this milestone?')">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline">Remove</button>
                                                    </form>
                                                @endunless
                                            @endcan
                                        </td>
                                    @endcanany
                                </tr>
                                @can('pm.milestones.release')
                                    @unless($milestone->isReleased())
                                        <tr>
                                            <td colspan="9" class="nested-row">
                                                <details class="settle-expand">
                                                    <summary>
                                                        <span><strong>Release</strong> · {{ $currency }} {{ number_format((float) $milestone->amount, 2) }}</span>
                                                        <span class="form-meta">Open to confirm</span>
                                                    </summary>
                                                    <div class="settle-expand-body">
                                                        <form method="POST" action="{{ route('admin.crm.deals.milestones.release', [$deal, $milestone]) }}">
                                                            @csrf
                                                            <p class="settle-form-lead">
                                                                Confirm portal release.
                                                                @if($split['platform_fee'] > 0)
                                                                    {{ $project->source?->name ?? 'Portal' }} commission {{ rtrim(rtrim(number_format($split['platform_percent'], 2), '0'), '.') }}%
                                                                    ({{ $currency }} {{ number_format($split['platform_fee'], 2) }}) deducted.
                                                                    Accounts records {{ $currency }} {{ number_format($split['net'], 2) }}.
                                                                @endif
                                                            </p>
                                                            <div class="form-grid">
                                                                <div class="form-group"><label>Release amount *</label><input type="number" step="0.01" min="0.01" name="amount" value="{{ $milestone->amount }}" required></div>
                                                                <div class="form-group"><label>Released at *</label><input type="datetime-local" name="released_at" value="{{ now()->format('Y-m-d\\TH:i') }}" required></div>
                                                                <div class="form-group"><label>Portal milestone ID</label><input type="text" name="portal_milestone_id"></div>
                                                                <div class="form-group"><label>Notes</label><input type="text" name="release_notes"></div>
                                                            </div>
                                                            <div class="form-actions" style="margin-top:12px;padding-top:0;border-top:none">
                                                                <button type="submit" class="btn btn-accent" onclick="return confirm('Notify Accounts to record this payment?')">Release milestone</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </details>
                                            </td>
                                        </tr>
                                    @endunless
                                @endcan
                            @empty
                                <tr><td colspan="9" class="empty-state">No milestones yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @can('pm.milestones.manage')
                    <form method="POST" action="{{ route('admin.crm.deals.milestones.store', $deal) }}" class="inline-form form-narrow">
                        @csrf
                        <h3>Add milestone</h3>
                        <div class="form-grid">
                            <div class="form-group"><label>Title *</label><input type="text" name="title" required maxlength="255"></div>
                            <div class="form-group"><label>Due date</label><input type="date" name="due_date"></div>
                            <div class="form-group"><label>Amount</label><input type="number" step="0.01" min="0" name="amount"></div>
                            <div class="form-group">
                                <label>Status</label>
                                <select name="status">
                                    @foreach(['pending', 'in_progress', 'completed'] as $status)
                                        <option value="{{ $status }}">{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-actions" style="margin-top:12px;padding-top:0;border-top:none">
                            <button type="submit" class="btn btn-accent">Add milestone</button>
                        </div>
                    </form>
                @endcan
            </div>
        </div>
    @endif
</div>
@endsection
