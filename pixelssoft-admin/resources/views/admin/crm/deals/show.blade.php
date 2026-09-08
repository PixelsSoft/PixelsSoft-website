@extends('admin.layout')

@section('title', $deal->title)

@section('content')
@php
    $project = $deal->project;
    $currency = $project?->currency ?: ($deal->currency ?: 'USD');
    $finance = $project ? $project->financeSummary() : null;
@endphp
<div class="card">
    <div class="card-header">
        <h2>{{ $deal->title }}</h2>
        <div>
            <a href="{{ route('admin.crm.deals.kanban') }}" class="btn btn-sm btn-outline">Pipeline</a>
            @can('crm.deals.edit')
                <a href="{{ route('admin.crm.deals.edit', $deal) }}" class="btn btn-sm btn-outline">Edit deal</a>
            @endcan
        </div>
    </div>
    <div class="form-grid" style="padding:0 24px 24px">
        <div><strong>Stage:</strong> {{ $deal->stage?->name ?? '—' }}</div>
        <div><strong>Company:</strong> {{ $deal->company?->name ?? '—' }}</div>
        <div><strong>Source:</strong> {{ $deal->acquisitionSource?->name ?? '—' }}</div>
        <div><strong>Value:</strong> {{ $currency }} {{ number_format((float) $deal->value, 2) }}</div>
        <div><strong>Sales:</strong> {{ $deal->salesperson?->name ?? '—' }}</div>
        <div><strong>Portal ID:</strong> {{ $deal->portal_contract_id ?: ($deal->lead?->portal_contract_id ?: '—') }}</div>
        <div><strong>Portal URL:</strong>
            @php $portalUrl = $deal->portal_url ?: $deal->lead?->portal_url; @endphp
            @if($portalUrl)
                <a href="{{ $portalUrl }}" target="_blank" rel="noopener">Open job</a>
            @else
                —
            @endif
        </div>
        @if($project)
            <div><strong>Delivery project:</strong> {{ $project->code }} · {{ $project->name }}</div>
        @endif
    </div>
    @if($deal->notes)
        <div style="padding:0 24px 24px"><strong>Notes:</strong><p style="margin-top:8px;color:#6b7280">{{ $deal->notes }}</p></div>
    @endif
</div>

@if(!$project)
    <div class="card">
        <div class="empty-state" style="padding:32px">
            <p>Win this deal on the pipeline to create a delivery project. Then you can add and release milestones here.</p>
        </div>
    </div>
@else
    <div class="stats-grid">
        <div class="stat-card"><div class="stat-label">Contract</div><div class="stat-value">{{ $currency }} {{ number_format($finance['contract'], 0) }}</div></div>
        <div class="stat-card"><div class="stat-label">Released</div><div class="stat-value">{{ $currency }} {{ number_format($finance['released_gross'], 0) }}</div></div>
        <div class="stat-card"><div class="stat-label">Platform fees</div><div class="stat-value">{{ $currency }} {{ number_format($finance['platform_fees'], 0) }}</div></div>
        <div class="stat-card"><div class="stat-label">Net received</div><div class="stat-value">{{ $currency }} {{ number_format($finance['net_received'], 0) }}</div></div>
        <div class="stat-card"><div class="stat-label">Sales commission</div><div class="stat-value">{{ $currency }} {{ number_format($finance['sales_commissions'], 0) }}</div></div>
        <div class="stat-card"><div class="stat-label">Remaining</div><div class="stat-value">{{ $currency }} {{ number_format($finance['remaining'], 0) }}</div></div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2>Milestones ({{ $project->milestones->count() }})</h2>
        </div>
        <div class="page-help">
            <p>Sales creates and releases milestones here. Accounts records which wallet the money landed in.</p>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Due</th>
                        <th>Gross</th>
                        <th>Platform</th>
                        <th>Net (expected)</th>
                        <th>Sales</th>
                        <th>Status</th>
                        <th>Released</th>
                        @canany(['pm.milestones.manage', 'pm.milestones.release'])<th>Actions</th>@endcanany
                    </tr>
                </thead>
                <tbody>
                    @forelse($project->milestones as $milestone)
                        @php $split = $preview->split((float) $milestone->amount, $project); @endphp
                        <tr>
                            <td><strong>{{ $milestone->title }}</strong></td>
                            <td>{{ $milestone->due_date?->format('M d, Y') ?? '—' }}</td>
                            <td>{{ $currency }} {{ number_format($milestone->amount, 2) }}</td>
                            <td>
                                @if($milestone->isReleased())
                                    {{ $currency }} {{ number_format($milestone->platform_fee_amount, 2) }}
                                @else
                                    {{ $currency }} {{ number_format($split['platform_fee'], 2) }}
                                @endif
                            </td>
                            <td>
                                @if($milestone->isSettled())
                                    {{ $milestone->received_currency ?: $currency }} {{ number_format($milestone->received_amount ?: $milestone->net_amount, 2) }}
                                    <div style="font-size:12px;color:#6b7280">{{ $milestone->paymentAccount?->name }}</div>
                                @else
                                    {{ $currency }} {{ number_format($milestone->isReleased() ? $milestone->net_amount : $split['net'], 2) }}
                                @endif
                            </td>
                            <td>
                                @if($milestone->isReleased())
                                    {{ $currency }} {{ number_format($milestone->sales_commission_amount, 2) }}
                                @else
                                    {{ $currency }} {{ number_format($split['sales_commission'], 2) }}
                                @endif
                            </td>
                            <td><span class="badge {{ $milestone->isSettled() ? 'badge-published' : ($milestone->isAwaitingSettlement() ? 'badge-unread' : 'badge-draft') }}">{{ str_replace('_', ' ', $milestone->status) }}</span></td>
                            <td>
                                @if($milestone->released_at)
                                    {{ $milestone->released_at->format('M d, Y H:i') }}
                                    <div style="font-size:12px;color:#6b7280">{{ $milestone->releasedBy?->name }}</div>
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
                                        <form method="POST" action="{{ route('admin.crm.deals.milestones.release', [$deal, $milestone]) }}">
                                            @csrf
                                            <p class="settle-form-lead">
                                                Confirm the portal release of {{ $currency }} {{ number_format((float) $milestone->amount, 2) }}.
                                                @if($split['platform_fee'] > 0)
                                                    {{ $project->source?->name ?? 'Portal' }} commission {{ rtrim(rtrim(number_format($split['platform_percent'], 2), '0'), '.') }}% ({{ $currency }} {{ number_format($split['platform_fee'], 2) }}) is deducted automatically.
                                                    Accounts will record {{ $currency }} {{ number_format($split['net'], 2) }}.
                                                @else
                                                    Accounts will choose the wallet and record the amount that landed.
                                                @endif
                                            </p>
                                            <div class="form-grid">
                                                <div class="form-group"><label>Release amount *</label><input type="number" step="0.01" min="0.01" name="amount" value="{{ $milestone->amount }}" required></div>
                                                <div class="form-group"><label>Released at *</label><input type="datetime-local" name="released_at" value="{{ now()->format('Y-m-d\\TH:i') }}" required></div>
                                                <div class="form-group"><label>Portal milestone ID</label><input type="text" name="portal_milestone_id"></div>
                                                <div class="form-group"><label>Notes</label><input type="text" name="release_notes"></div>
                                            </div>
                                            <div class="form-actions" style="margin-top:12px;padding-top:0;border-top:none">
                                                <button type="submit" class="btn btn-primary" onclick="return confirm('Notify Accounts to record this payment?')">Release milestone</button>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                            @endunless
                        @endcan
                    @empty
                        <tr><td colspan="9" class="empty-state">No milestones yet. Add the first one below.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @can('pm.milestones.manage')
            <form method="POST" action="{{ route('admin.crm.deals.milestones.store', $deal) }}" style="padding:0 24px 24px">
                @csrf
                <h3 style="margin:16px 0 12px;font-size:15px">Add Milestone</h3>
                <div class="form-grid">
                    <div class="form-group"><label>Title *</label><input type="text" name="title" required maxlength="255"></div>
                    <div class="form-group"><label>Due Date</label><input type="date" name="due_date"></div>
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
                    <button type="submit" class="btn btn-primary">Add Milestone</button>
                </div>
            </form>
        @endcan
    </div>
@endif
@endsection
