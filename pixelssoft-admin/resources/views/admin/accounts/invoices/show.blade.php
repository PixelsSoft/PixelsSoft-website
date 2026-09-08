@extends('admin.layout')

@section('title', $invoice->number)

@section('content')
@php
    $sourceName = $invoice->sourceName();
    $portalUrl = $invoice->portalUrl();
    $portalId = $invoice->portalContractId();
    $clientAmount = $invoice->clientAmount();
    $fees = $invoice->displayedFees();
    $outstanding = $invoice->outstanding();
    $receivedDisplay = $invoice->payments->sum('amount');
    $canEditItems = auth()->user()->can('accounts.invoices.edit') && $invoice->isUnpaid();
    $canRecordPayment = auth()->user()->can('accounts.payments.create') && ! $invoice->isClosed();
@endphp

<div class="invoice-sheet" data-ui-tabs>
    <div class="page-header">
        <div class="page-header-main">
            <div class="page-kicker">Invoice</div>
            <h1>{{ $invoice->number }}</h1>
            <div class="page-header-meta">
                <span class="badge {{ $invoice->statusBadge() }}">{{ $invoice->displayStatus() }}</span>
                <span class="form-meta">{{ $invoice->currency }} · Due {{ $invoice->due_date?->format('M d, Y') ?? '—' }}</span>
            </div>
        </div>
        <div class="page-header-actions">
            @can('accounts.invoices.edit')
                <a href="{{ route('admin.accounts.invoices.pdf', $invoice) }}" class="btn btn-sm btn-outline">PDF</a>
                <a href="{{ route('admin.accounts.invoices.edit', $invoice) }}" class="btn btn-sm btn-outline">Edit</a>
            @endcan
            @can('accounts.invoices.delete')
                <form action="{{ route('admin.accounts.invoices.destroy', $invoice) }}" method="POST" style="display:inline" onsubmit="return confirm('Delete this invoice?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline">Delete</button>
                </form>
            @endcan
        </div>
    </div>

    <div class="stats-grid stats-grid-sm">
        <div class="stat-card">
            <div class="stat-label">Client amount</div>
            <div class="stat-value">{{ $invoice->currency }} {{ number_format($clientAmount, 0) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Fees</div>
            <div class="stat-value">{{ $invoice->currency }} {{ number_format($fees, 0) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Received</div>
            <div class="stat-value">{{ $invoice->currency }} {{ number_format($invoice->isClosed() ? ($clientAmount - $fees) : $receivedDisplay, 0) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Balance due</div>
            <div class="stat-value">{{ $invoice->currency }} {{ number_format($outstanding, 0) }}</div>
        </div>
    </div>

    <div class="ui-tabs" role="tablist">
        <button type="button" class="ui-tab is-active" data-tab="overview" role="tab">Overview</button>
        <button type="button" class="ui-tab" data-tab="items" role="tab">Items ({{ $invoice->items->count() }})</button>
        <button type="button" class="ui-tab" data-tab="payments" role="tab">Payments ({{ $invoice->payments->count() }})</button>
        @if($invoice->publicPayUrl())
            <button type="button" class="ui-tab" data-tab="share" role="tab">Share</button>
        @endif
    </div>

    <div class="ui-tab-panel is-active" data-panel="overview">
        <div class="card">
            <div class="card-header"><h2>Details</h2></div>
            <div class="dl-grid">
                <div>
                    <span class="dl-label">Company</span>
                    <div class="dl-value">{{ $invoice->company?->name ?? '—' }}</div>
                </div>
                <div>
                    <span class="dl-label">Deal</span>
                    <div class="dl-value">
                        @if($invoice->deal)
                            <a href="{{ route('admin.crm.deals.show', $invoice->deal) }}">{{ $invoice->deal->title }}</a>
                        @else
                            —
                        @endif
                    </div>
                </div>
                <div>
                    <span class="dl-label">Source</span>
                    <div class="dl-value">{{ $sourceName ?: '—' }}</div>
                </div>
                <div>
                    <span class="dl-label">Portal / job</span>
                    <div class="dl-value">
                        @if($portalUrl)
                            <a href="{{ $portalUrl }}" target="_blank" rel="noopener" class="invoice-url">Open job</a>
                        @else
                            —
                        @endif
                        @if($portalId)
                            <div class="form-meta">ID {{ $portalId }}</div>
                        @endif
                    </div>
                </div>
                <div>
                    <span class="dl-label">Issue date</span>
                    <div class="dl-value">{{ $invoice->issue_date->format('M d, Y') }}</div>
                </div>
                <div>
                    <span class="dl-label">Due date</span>
                    <div class="dl-value">{{ $invoice->due_date?->format('M d, Y') ?? '—' }}</div>
                </div>
                <div>
                    <span class="dl-label">Currency</span>
                    <div class="dl-value">{{ $invoice->currency }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="ui-tab-panel" data-panel="items">
        <div class="table-card">
            <div class="card-header"><h2>Line items</h2></div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Description</th>
                            <th class="text-right">Qty</th>
                            <th class="text-right">Unit price</th>
                            <th class="text-right">Amount</th>
                            @if($canEditItems)<th></th>@endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invoice->items as $item)
                            <tr class="{{ (float) $item->amount < 0 ? 'line-deduction' : '' }}">
                                <td>{{ $item->description }}</td>
                                <td class="text-right num">{{ number_format($item->quantity, 2) }}</td>
                                <td class="text-right num">{{ $invoice->currency }} {{ number_format($item->unit_price, 2) }}</td>
                                <td class="text-right num">{{ $invoice->currency }} {{ number_format($item->amount, 2) }}</td>
                                @if($canEditItems)
                                    <td>
                                        <form action="{{ route('admin.accounts.invoices.items.destroy', [$invoice, $item]) }}" method="POST" style="display:inline" onsubmit="return confirm('Remove this line item?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline">Remove</button>
                                        </form>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr><td colspan="{{ $canEditItems ? 5 : 4 }}" class="empty-state">No line items yet.</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-right">Client amount</td>
                            <td class="text-right num">{{ $invoice->currency }} {{ number_format($clientAmount, 2) }}</td>
                            @if($canEditItems)<td></td>@endif
                        </tr>
                        <tr>
                            <td colspan="3" class="text-right">Fees</td>
                            <td class="text-right num line-deduction">{{ $invoice->currency }} {{ number_format($fees, 2) }}</td>
                            @if($canEditItems)<td></td>@endif
                        </tr>
                        <tr>
                            <td colspan="3" class="text-right"><strong>Total</strong></td>
                            <td class="text-right num"><strong>{{ $invoice->currency }} {{ number_format($invoice->isClosed() ? ($clientAmount - $fees) : $invoice->total, 2) }}</strong></td>
                            @if($canEditItems)<td></td>@endif
                        </tr>
                    </tfoot>
                </table>
            </div>

            @if($canEditItems)
                <form method="POST" action="{{ route('admin.accounts.invoices.items.store', $invoice) }}" class="inline-form form-narrow">
                    @csrf
                    <h3>Add line item</h3>
                    <div class="form-grid">
                        <div class="form-group"><label>Description *</label><input type="text" name="description" required></div>
                        <div class="form-group"><label>Quantity *</label><input type="number" step="0.01" min="0.01" name="quantity" value="1" required></div>
                        <div class="form-group"><label>Unit price *</label><input type="number" step="0.01" min="0" name="unit_price" required></div>
                    </div>
                    <div class="form-actions" style="margin-top:12px;padding-top:0;border-top:none">
                        <button type="submit" class="btn btn-accent">Add item</button>
                    </div>
                </form>
            @endif
        </div>
    </div>

    <div class="ui-tab-panel" data-panel="payments">
        <div class="table-card">
            <div class="card-header"><h2>Wallet receipts</h2></div>
            <div class="page-help">
                <p>Amount that actually landed. Platform and processor cuts are fees, not client balance.</p>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th class="text-right">Received</th>
                            <th>Wallet</th>
                            <th>Method</th>
                            <th>Reference</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invoice->payments as $payment)
                            <tr>
                                <td>{{ $payment->paid_at->format('M d, Y H:i') }}</td>
                                <td class="text-right num">{{ $payment->currency ?: $invoice->currency }} {{ number_format($payment->amount, 2) }}</td>
                                <td>
                                    @if($payment->paymentAccount && auth()->user()->can('accounts.ledger.view'))
                                        <a href="{{ route('admin.accounts.ledger.show', $payment->paymentAccount) }}">{{ $payment->paymentAccount->name }}</a>
                                    @elseif($payment->paymentAccount)
                                        {{ $payment->paymentAccount->name }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>{{ str_replace('_', ' ', $payment->method) }}</td>
                                <td>{{ $payment->reference ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="empty-state">No payments recorded.</td></tr>
                        @endforelse
                    </tbody>
                    @if($invoice->payments->count())
                        <tfoot>
                            <tr>
                                <td class="text-right"><strong>Balance due</strong></td>
                                <td class="text-right num" colspan="4"><strong>{{ $invoice->currency }} {{ number_format($outstanding, 2) }}</strong></td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>

            @if($canRecordPayment)
                <form method="POST" action="{{ route('admin.accounts.payments.store', $invoice) }}" class="inline-form form-narrow">
                    @csrf
                    <h3>Record payment</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Amount *</label>
                            <input type="number" step="0.01" min="0.01" max="{{ number_format($outstanding, 2, '.', '') }}" name="amount" value="{{ number_format($outstanding, 2, '.', '') }}" required>
                            <span class="settle-amount-hint">Max {{ $invoice->currency }} {{ number_format($outstanding, 2) }}</span>
                        </div>
                        <div class="form-group">
                            <label>Method *</label>
                            <select name="method" required>
                                @foreach(['bank_transfer','cash','card','check','other'] as $method)
                                    <option value="{{ $method }}">{{ ucfirst(str_replace('_', ' ', $method)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group"><label>Reference</label><input type="text" name="reference" maxlength="100"></div>
                        <div class="form-group"><label>Paid at *</label><input type="datetime-local" name="paid_at" value="{{ now()->format('Y-m-d\\TH:i') }}" required></div>
                        <div class="form-group">
                            <label>Wallet</label>
                            <select name="payment_account_id">
                                <option value="">— None —</option>
                                @foreach(\App\Models\Accounts\PaymentAccount::where('is_active', true)->orderBy('name')->get() as $account)
                                    <option value="{{ $account->id }}">{{ $account->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-actions" style="margin-top:12px;padding-top:0;border-top:none">
                        <button type="submit" class="btn btn-accent">Record payment</button>
                    </div>
                </form>
            @endif
        </div>
    </div>

    @if($invoice->publicPayUrl())
        <div class="ui-tab-panel" data-panel="share">
            <div class="card form-narrow">
                <div class="card-header"><h2>Shareable payment link</h2></div>
                <p class="settle-form-lead">Anyone with this link can open the invoice and pay by card. No login required.</p>
                <div class="share-link-row">
                    <input type="text" id="invoice-pay-url" value="{{ $invoice->publicPayUrl() }}" readonly>
                    <button type="button" class="btn btn-sm btn-accent" onclick="navigator.clipboard.writeText(document.getElementById('invoice-pay-url').value); this.textContent='Copied'">Copy</button>
                    <a href="{{ $invoice->publicPayUrl() }}" class="btn btn-sm btn-outline" target="_blank" rel="noopener">Open</a>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
