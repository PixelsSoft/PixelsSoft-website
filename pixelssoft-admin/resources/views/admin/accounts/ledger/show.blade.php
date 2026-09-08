@extends('admin.layout')

@section('title', $account->name . ' ledger')

@section('content')
<div class="card">
    <div class="card-header">
        <h2>{{ $account->name }} ledger</h2>
        <div>
            @foreach($accounts as $other)
                <a href="{{ route('admin.accounts.ledger.show', $other) }}" class="btn btn-sm {{ $other->id === $account->id ? 'btn-primary' : 'btn-outline' }}">{{ $other->name }}</a>
            @endforeach
        </div>
    </div>
    <div class="stats-grid" style="padding:0 24px 8px">
        <div class="stat-card"><div class="stat-label">Money in</div><div class="stat-value">{{ $account->currency }} {{ number_format($moneyIn, 0) }}</div></div>
        <div class="stat-card"><div class="stat-label">Money out</div><div class="stat-value">{{ $account->currency }} {{ number_format($moneyOut, 0) }}</div></div>
        <div class="stat-card"><div class="stat-label">Balance</div><div class="stat-value">{{ $account->currency }} {{ number_format($balance, 0) }}</div></div>
    </div>
</div>

<div class="card">
    <div class="card-header"><h2>Milestones received into {{ $account->name }}</h2></div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Paid</th><th>Milestone</th><th>Project</th><th>Amount in this account</th><th>Reference</th></tr></thead>
            <tbody>
                @forelse($milestones as $payment)
                    <tr>
                        <td>{{ $payment->paid_at?->format('M d, Y H:i') }}</td>
                        <td>{{ $payment->milestone?->title ?? '—' }}</td>
                        <td>
                            @if($payment->project)
                                <a href="{{ route('admin.pm.projects.show', $payment->project) }}">{{ $payment->project->code }}</a>
                            @else
                                —
                            @endif
                        </td>
                        <td>{{ $payment->currency ?: $account->currency }} {{ number_format($payment->amount, 2) }}</td>
                        <td>{{ $payment->reference ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="empty-state">No milestone payments in this account yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <div class="card-header"><h2>Cash book</h2></div>
    @include('admin.partials.list-toolbar', [
        'showSearch' => true,
        'searchPlaceholder' => 'Search milestone or project…',
        'filters' => [],
    ])
    <div class="table-wrap">
        <table>
            <thead><tr><th>When</th><th>Type</th><th>Milestone / project</th><th>In</th><th>Out</th><th>Balance</th></tr></thead>
            <tbody>
                @forelse($rows as $entry)
                    <tr>
                        <td>{{ $entry->occurred_at->format('M d, Y H:i') }}</td>
                        <td><span class="badge badge-draft">{{ $entry->label() }}</span></td>
                        <td>
                            @if($entry->project)
                                <a href="{{ route('admin.pm.projects.show', $entry->project) }}">{{ $entry->project->code }}</a>
                            @endif
                            <div class="form-meta">{{ $entry->milestone?->title ?? $entry->description }}</div>
                        </td>
                        <td>
                            @if($entry->signed_amount > 0)
                                {{ $entry->currency }} {{ number_format($entry->amount, 2) }}
                            @else
                                —
                            @endif
                        </td>
                        <td>
                            @if($entry->signed_amount < 0)
                                {{ $entry->currency }} {{ number_format($entry->amount, 2) }}
                            @else
                                —
                            @endif
                        </td>
                        <td><strong>{{ $entry->currency }} {{ number_format($entry->running_balance, 2) }}</strong></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="empty-state">This ledger is empty until Accounts records a payment into {{ $account->name }}.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
