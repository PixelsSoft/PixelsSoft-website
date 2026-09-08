@extends('admin.layout')

@section('title', 'Accounts Dashboard')

@section('content')
<div class="page-header">
    <div class="page-header-main">
        <div class="page-kicker">Accounts</div>
        <h1>Finance overview</h1>
    </div>
    <div class="page-header-actions">
        @can('accounts.invoices.create')
            <a href="{{ route('admin.accounts.invoices.create') }}" class="btn btn-sm btn-accent">New invoice</a>
        @endcan
        @can('accounts.settlements.manage')
            @if(($stats['pending_settlements'] ?? 0) > 0)
                <a href="{{ route('admin.accounts.settlements.index') }}" class="btn btn-sm btn-outline">{{ $stats['pending_settlements'] }} pending</a>
            @endif
        @endcan
    </div>
</div>

<div class="stats-grid stats-grid-sm">
    <div class="stat-card">
        <div class="stat-label">Outstanding</div>
        <div class="stat-value">${{ number_format($stats['outstanding'], 0) }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Paid this month</div>
        <div class="stat-value">${{ number_format($stats['paid_this_month'], 0) }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Net received</div>
        <div class="stat-value">${{ number_format($stats['net_this_month'] ?? 0, 0) }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Overdue invoices</div>
        <div class="stat-value">{{ $stats['overdue'] }}</div>
    </div>
</div>

<div class="quick-actions">
    @can('accounts.invoices.create')
        <a href="{{ route('admin.accounts.invoices.create') }}" class="quick-action"><div class="qa-icon">+</div><div><strong>New Invoice</strong></div></a>
    @endcan
    @can('accounts.expenses.create')
        <a href="{{ route('admin.accounts.expenses.create') }}" class="quick-action"><div class="qa-icon">$</div><div><strong>Submit Expense</strong></div></a>
    @endcan
    <a href="{{ route('admin.accounts.payments.index') }}" class="quick-action"><div class="qa-icon">✓</div><div><strong>Payments</strong></div></a>
    @can('accounts.ledger.view')
        <a href="{{ route('admin.accounts.ledger.index') }}" class="quick-action"><div class="qa-icon">≡</div><div><strong>Ledger</strong></div></a>
    @endcan
    @can('accounts.commissions.view')
        <a href="{{ route('admin.accounts.commissions.index') }}" class="quick-action"><div class="qa-icon">%</div><div><strong>Commissions</strong></div></a>
    @endcan
</div>

@isset($wallets)
<div class="table-card">
    <div class="card-header">
        <h2>Wallets</h2>
        @can('accounts.payment-accounts.manage')
            <a href="{{ route('admin.accounts.wallets.index') }}" class="btn btn-sm btn-outline">Manage</a>
        @endcan
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Wallet</th>
                    <th>Provider</th>
                    <th>Currency</th>
                    <th class="text-right">Net received</th>
                    <th class="text-right">Sales paid out</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($wallets as $wallet)
                    <tr>
                        <td>
                            @can('accounts.ledger.view')
                                <a href="{{ route('admin.accounts.ledger.show', $wallet) }}"><strong>{{ $wallet->name }}</strong></a>
                            @else
                                <strong>{{ $wallet->name }}</strong>
                            @endcan
                        </td>
                        <td>{{ str_replace('_', ' ', $wallet->provider) }}</td>
                        <td>{{ $wallet->currency }}</td>
                        <td class="text-right num">{{ $wallet->currency }} {{ number_format($wallet->net_in ?? 0, 2) }}</td>
                        <td class="text-right num">{{ $wallet->currency }} {{ number_format($wallet->sales_out ?? 0, 2) }}</td>
                        <td>
                            @can('accounts.ledger.view')
                                <a href="{{ route('admin.accounts.ledger.show', $wallet) }}" class="btn btn-sm btn-outline">Ledger</a>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="empty-state">Add Wise, Payoneer, Pakistani bank, and Stripe under Wallets.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endisset

<div class="table-card">
    <div class="card-header">
        <h2>Recent invoices</h2>
        <a href="{{ route('admin.accounts.invoices.index') }}" class="btn btn-sm btn-outline">View all</a>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Number</th>
                    <th>Company</th>
                    <th>Status</th>
                    <th class="text-right">Total</th>
                    <th>Due</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentInvoices as $invoice)
                    <tr>
                        <td><a href="{{ route('admin.accounts.invoices.show', $invoice) }}">{{ $invoice->number }}</a></td>
                        <td>{{ $invoice->company?->name ?? '—' }}</td>
                        <td><span class="badge badge-draft">{{ $invoice->displayStatus() }}</span></td>
                        <td class="text-right num">{{ $invoice->currency }} {{ number_format($invoice->total, 2) }}</td>
                        <td>{{ $invoice->due_date?->format('M d, Y') ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="empty-state">No invoices yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@isset($recentLedger)
<div class="table-card">
    <div class="card-header">
        <h2>Recent ledger</h2>
        @can('accounts.ledger.view')
            <a href="{{ route('admin.accounts.ledger.index') }}" class="btn btn-sm btn-outline">View all</a>
        @endcan
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>When</th>
                    <th>Type</th>
                    <th class="text-right">Amount</th>
                    <th>Project</th>
                    <th>Wallet</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentLedger as $entry)
                    <tr>
                        <td>{{ $entry->occurred_at->format('M d, Y H:i') }}</td>
                        <td>{{ $entry->label() }}</td>
                        <td class="text-right num">{{ $entry->currency }} {{ number_format($entry->amount, 2) }}</td>
                        <td>{{ $entry->project?->code ?? '—' }}</td>
                        <td>
                            @if($entry->paymentAccount)
                                @can('accounts.ledger.view')
                                    <a href="{{ route('admin.accounts.ledger.show', $entry->paymentAccount) }}">{{ $entry->paymentAccount->name }}</a>
                                @else
                                    {{ $entry->paymentAccount->name }}
                                @endcan
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="empty-state">No ledger rows yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endisset

<div class="table-card">
    <div class="card-header">
        <h2>Pending expenses</h2>
        <a href="{{ route('admin.accounts.expenses.index') }}" class="btn btn-sm btn-outline">View all</a>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Submitter</th>
                    <th>Category</th>
                    <th class="text-right">Amount</th>
                    <th>Vendor</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pendingExpenses as $expense)
                    <tr>
                        <td>{{ $expense->date->format('M d, Y') }}</td>
                        <td>{{ $expense->submitter?->name ?? '—' }}</td>
                        <td>{{ $expense->category?->name ?? '—' }}</td>
                        <td class="text-right num">${{ number_format($expense->amount, 2) }}</td>
                        <td>{{ $expense->vendor ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="empty-state">No pending expenses.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
