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
@endphp
<div class="invoice-sheet">
    <div class="card invoice-hero">
        <div class="invoice-hero-main">
            <div class="invoice-kicker">Invoice</div>
            <h2>{{ $invoice->number }}</h2>
            <span class="badge {{ $invoice->statusBadge() }}">{{ strtoupper($invoice->status) }}</span>
        </div>
        <div class="invoice-hero-actions">
            @can('accounts.invoices.edit')
                <a href="{{ route('admin.accounts.invoices.pdf', $invoice) }}" class="btn btn-sm btn-outline">Download PDF</a>
                @if($invoice->status === 'draft')
                    <form action="{{ route('admin.accounts.invoices.sent', $invoice) }}" method="POST" style="display:inline">
                        @csrf @method('PATCH')
                        <button type="submit" class="btn btn-sm btn-primary">Mark Sent</button>
                    </form>
                @endif
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

    <div class="card">
        <div class="invoice-meta">
            <div>
                <span class="invoice-meta-label">Company</span>
                <strong>{{ $invoice->company?->name ?? '—' }}</strong>
            </div>
            <div>
                <span class="invoice-meta-label">Project</span>
                <strong>
                    @if($invoice->project)
                        <a href="{{ route('admin.pm.projects.show', $invoice->project) }}">{{ $invoice->project->name }}</a>
                    @else
                        —
                    @endif
                </strong>
            </div>
            <div>
                <span class="invoice-meta-label">Source</span>
                <strong>{{ $sourceName ?: '—' }}</strong>
            </div>
            <div>
                <span class="invoice-meta-label">Portal / job URL</span>
                <strong>
                    @if($portalUrl)
                        <a href="{{ $portalUrl }}" target="_blank" rel="noopener" class="invoice-url">Open job</a>
                    @else
                        —
                    @endif
                </strong>
                @if($portalId)
                    <div class="form-meta">ID {{ $portalId }}</div>
                @endif
            </div>
            <div>
                <span class="invoice-meta-label">Issue date</span>
                <strong>{{ $invoice->issue_date->format('M d, Y') }}</strong>
            </div>
            <div>
                <span class="invoice-meta-label">Due date</span>
                <strong>{{ $invoice->due_date?->format('M d, Y') ?? '—' }}</strong>
            </div>
            <div>
                <span class="invoice-meta-label">Currency</span>
                <strong>{{ $invoice->currency }}</strong>
            </div>
            <div>
                <span class="invoice-meta-label">Deal</span>
                <strong>
                    @php
                        $dealTitle = $invoice->deal?->title ?? $invoice->project?->deal?->title;
                        $dealModel = $invoice->deal ?? $invoice->project?->deal;
                    @endphp
                    @if($dealModel && auth()->user()->can('crm.deals.view'))
                        <a href="{{ route('admin.crm.deals.show', $dealModel) }}">{{ $dealTitle }}</a>
                    @else
                        {{ $dealTitle ?: '—' }}
                    @endif
                </strong>
            </div>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">Released / client</div>
            <div class="stat-value">{{ $invoice->currency }} {{ number_format($clientAmount, 0) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Platform + processor fees</div>
            <div class="stat-value">{{ $invoice->currency }} {{ number_format($fees, 0) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Received in wallet</div>
            <div class="stat-value">{{ $invoice->currency }} {{ number_format($invoice->isClosed() ? ($clientAmount - $fees) : $receivedDisplay, 0) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Balance due</div>
            <div class="stat-value">{{ $invoice->currency }} {{ number_format($outstanding, 0) }}</div>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h2>Line items</h2></div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Description</th>
                        <th>Qty</th>
                        <th>Unit price</th>
                        <th>Amount</th>
                        @if(auth()->user()->can('accounts.invoices.edit') && $invoice->status === 'draft')
                            <th></th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoice->items as $item)
                        <tr class="{{ (float) $item->amount < 0 ? 'line-deduction' : '' }}">
                            <td>{{ $item->description }}</td>
                            <td>{{ number_format($item->quantity, 2) }}</td>
                            <td>{{ $invoice->currency }} {{ number_format($item->unit_price, 2) }}</td>
                            <td>{{ $invoice->currency }} {{ number_format($item->amount, 2) }}</td>
                            @if(auth()->user()->can('accounts.invoices.edit') && $invoice->status === 'draft')
                                <td>
                                    <form action="{{ route('admin.accounts.invoices.items.destroy', [$invoice, $item]) }}" method="POST" style="display:inline" onsubmit="return confirm('Remove this line item?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline">Remove</button>
                                    </form>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr><td colspan="4" class="empty-state">No line items yet.</td></tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr><td colspan="3" style="text-align:right">Client amount</td><td>{{ $invoice->currency }} {{ number_format($clientAmount, 2) }}</td></tr>
                    <tr><td colspan="3" style="text-align:right">Fees (platform / processor)</td><td class="line-deduction">{{ $invoice->currency }} {{ number_format($fees, 2) }}</td></tr>
                    <tr><td colspan="3" style="text-align:right"><strong>Received / total</strong></td><td><strong>{{ $invoice->currency }} {{ number_format($invoice->isClosed() ? ($clientAmount - $fees) : $invoice->total, 2) }}</strong></td></tr>
                </tfoot>
            </table>
        </div>

        @can('accounts.invoices.edit')
            @if($invoice->status === 'draft')
                <form method="POST" action="{{ route('admin.accounts.invoices.items.store', $invoice) }}" style="padding:0 24px 24px">
                    @csrf
                    <h3 style="margin:16px 0 12px;font-size:15px">Add Line Item</h3>
                    <div class="form-grid">
                        <div class="form-group"><label>Description *</label><input type="text" name="description" required></div>
                        <div class="form-group"><label>Quantity *</label><input type="number" step="0.01" min="0.01" name="quantity" value="1" required></div>
                        <div class="form-group"><label>Unit Price *</label><input type="number" step="0.01" min="0" name="unit_price" required></div>
                    </div>
                    <div class="form-actions" style="margin-top:12px;padding-top:0;border-top:none">
                        <button type="submit" class="btn btn-primary">Add Item</button>
                    </div>
                </form>
            @endif
        @endcan
    </div>

    <div class="card">
        <div class="card-header"><h2>Wallet receipts ({{ $invoice->payments->count() }})</h2></div>
        <div class="page-help">
            <p>The amount recorded here is what actually landed. Platform and Wise/Payoneer cuts are fees, not a client balance.</p>
        </div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Date</th><th>Amount received</th><th>Wallet</th><th>Method</th><th>Reference</th></tr></thead>
                <tbody>
                    @forelse($invoice->payments as $payment)
                        <tr>
                            <td>{{ $payment->paid_at->format('M d, Y H:i') }}</td>
                            <td>{{ $payment->currency ?: $invoice->currency }} {{ number_format($payment->amount, 2) }}</td>
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
                            <td style="text-align:right"><strong>Balance due</strong></td>
                            <td colspan="4"><strong>{{ $invoice->currency }} {{ number_format($outstanding, 2) }}</strong></td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>

        @if(auth()->user()->can('accounts.payments.create') && ! $invoice->isClosed())
                <form method="POST" action="{{ route('admin.accounts.payments.store', $invoice) }}" style="padding:0 24px 24px">
                    @csrf
                    <h3 style="margin:16px 0 12px;font-size:15px">Record Payment</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Amount *</label>
                            <input type="number" step="0.01" min="0.01" max="{{ number_format($outstanding, 2, '.', '') }}" name="amount" value="{{ number_format($outstanding, 2, '.', '') }}" required>
                            <span class="settle-amount-hint">Cannot exceed balance due {{ $invoice->currency }} {{ number_format($outstanding, 2) }}</span>
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
                        <div class="form-group"><label>Paid At *</label><input type="datetime-local" name="paid_at" value="{{ now()->format('Y-m-d\\TH:i') }}" required></div>
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
                        <button type="submit" class="btn btn-primary">Record Payment</button>
                    </div>
                </form>
        @endif
    </div>
</div>
@endsection
