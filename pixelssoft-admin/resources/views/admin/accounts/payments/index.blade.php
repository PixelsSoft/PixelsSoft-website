@extends('admin.layout')

@section('title', 'Payments')

@section('content')
<div class="card">
    <div class="card-header">
        <h2>Payments</h2>
        <a href="{{ route('admin.accounts.invoices.index') }}" class="btn btn-sm btn-outline">Invoices</a>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Date</th><th>Invoice</th><th>Company</th><th>Amount</th><th>Method</th><th>Reference</th></tr></thead>
            <tbody>
                @forelse($payments as $payment)
                    <tr>
                        <td>{{ $payment->paid_at->format('M d, Y') }}</td>
                        <td>
                            @can('accounts.invoices.view')
                                <a href="{{ route('admin.accounts.invoices.show', $payment->invoice) }}">{{ $payment->invoice?->number ?? '—' }}</a>
                            @else
                                {{ $payment->invoice?->number ?? '—' }}
                            @endcan
                        </td>
                        <td>{{ $payment->invoice?->company?->name ?? '—' }}</td>
                        <td>{{ $payment->invoice?->currency ?? 'USD' }} {{ number_format($payment->amount, 2) }}</td>
                        <td>{{ str_replace('_', ' ', $payment->method) }}</td>
                        <td>{{ $payment->reference ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="empty-state">No payments yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $payments->links() }}
</div>
@endsection
