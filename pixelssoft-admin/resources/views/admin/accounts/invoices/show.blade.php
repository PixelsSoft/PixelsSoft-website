@extends('admin.layout')

@section('title', $invoice->number)

@section('content')
<div class="card">
    <div class="card-header">
        <h2>Invoice {{ $invoice->number }}</h2>
        <div>
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
    <div class="form-grid" style="padding:0 24px 24px">
        <div><strong>Status:</strong> <span class="badge badge-draft">{{ $invoice->status }}</span></div>
        <div><strong>Company:</strong> {{ $invoice->company?->name ?? '—' }}</div>
        <div><strong>Project:</strong> {{ $invoice->project?->name ?? '—' }}</div>
        <div><strong>Issue Date:</strong> {{ $invoice->issue_date->format('M d, Y') }}</div>
        <div><strong>Due Date:</strong> {{ $invoice->due_date?->format('M d, Y') ?? '—' }}</div>
        <div><strong>Currency:</strong> {{ $invoice->currency }}</div>
    </div>
    @if($invoice->notes)
        <div style="padding:0 24px 24px"><strong>Notes:</strong><p style="margin-top:8px;color:#6b7280">{{ $invoice->notes }}</p></div>
    @endif
</div>

<div class="card">
    <div class="card-header"><h2>Line Items</h2></div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Description</th><th>Qty</th><th>Unit Price</th><th>Amount</th>@can('accounts.invoices.edit')<th></th>@endcan</tr></thead>
            <tbody>
                @forelse($invoice->items as $item)
                    <tr>
                        <td>{{ $item->description }}</td>
                        <td>{{ number_format($item->quantity, 2) }}</td>
                        <td>{{ $invoice->currency }} {{ number_format($item->unit_price, 2) }}</td>
                        <td>{{ $invoice->currency }} {{ number_format($item->amount, 2) }}</td>
                        @can('accounts.invoices.edit')
                            <td>
                                <form action="{{ route('admin.accounts.invoices.items.destroy', [$invoice, $item]) }}" method="POST" style="display:inline" onsubmit="return confirm('Remove this line item?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline">Remove</button>
                                </form>
                            </td>
                        @endcan
                    </tr>
                @empty
                    <tr><td colspan="{{ auth()->user()->can('accounts.invoices.edit') ? 5 : 4 }}" class="empty-state">No line items yet.</td></tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr><td colspan="3" style="text-align:right"><strong>Subtotal</strong></td><td>{{ $invoice->currency }} {{ number_format($invoice->subtotal, 2) }}</td>@can('accounts.invoices.edit')<td></td>@endcan</tr>
                <tr><td colspan="3" style="text-align:right"><strong>Tax</strong></td><td>{{ $invoice->currency }} {{ number_format($invoice->tax, 2) }}</td>@can('accounts.invoices.edit')<td></td>@endcan</tr>
                <tr><td colspan="3" style="text-align:right"><strong>Total</strong></td><td><strong>{{ $invoice->currency }} {{ number_format($invoice->total, 2) }}</strong></td>@can('accounts.invoices.edit')<td></td>@endcan</tr>
            </tfoot>
        </table>
    </div>

    @can('accounts.invoices.edit')
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
    @endcan
</div>

<div class="card">
    <div class="card-header"><h2>Payments ({{ $invoice->payments->count() }})</h2></div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Date</th><th>Amount</th><th>Method</th><th>Reference</th></tr></thead>
            <tbody>
                @php $paidTotal = 0; @endphp
                @forelse($invoice->payments as $payment)
                    @php $paidTotal += $payment->amount; @endphp
                    <tr>
                        <td>{{ $payment->paid_at->format('M d, Y') }}</td>
                        <td>{{ $invoice->currency }} {{ number_format($payment->amount, 2) }}</td>
                        <td>{{ str_replace('_', ' ', $payment->method) }}</td>
                        <td>{{ $payment->reference ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="empty-state">No payments recorded.</td></tr>
                @endforelse
            </tbody>
            @if($invoice->payments->count())
                <tfoot>
                    <tr><td style="text-align:right"><strong>Paid</strong></td><td><strong>{{ $invoice->currency }} {{ number_format($paidTotal, 2) }}</strong></td><td colspan="2"><strong>Balance: {{ $invoice->currency }} {{ number_format(max($invoice->total - $paidTotal, 0), 2) }}</strong></td></tr>
                </tfoot>
            @endif
        </table>
    </div>

    @can('accounts.payments.create')
        @if($invoice->status !== 'paid' && $invoice->status !== 'void')
            <form method="POST" action="{{ route('admin.accounts.payments.store', $invoice) }}" style="padding:0 24px 24px">
                @csrf
                <h3 style="margin:16px 0 12px;font-size:15px">Record Payment</h3>
                <div class="form-grid">
                    <div class="form-group"><label>Amount *</label><input type="number" step="0.01" min="0.01" name="amount" required></div>
                    <div class="form-group">
                        <label>Method *</label>
                        <select name="method" required>
                            @foreach(['bank_transfer','cash','card','check','other'] as $method)
                                <option value="{{ $method }}">{{ ucfirst(str_replace('_', ' ', $method)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group"><label>Reference</label><input type="text" name="reference" maxlength="100"></div>
                    <div class="form-group"><label>Paid At *</label><input type="date" name="paid_at" value="{{ now()->format('Y-m-d') }}" required></div>
                </div>
                <div class="form-actions" style="margin-top:12px;padding-top:0;border-top:none">
                    <button type="submit" class="btn btn-primary">Record Payment</button>
                </div>
            </form>
        @endif
    @endcan
</div>
@endsection
