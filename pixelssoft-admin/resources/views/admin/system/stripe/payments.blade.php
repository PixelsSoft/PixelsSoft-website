@extends('admin.layout')

@section('title', 'Card payments')

@section('content')
<div class="card">
    <div class="card-header">
        <h2>Card payment details</h2>
        <span class="badge badge-unread">Super Admin only</span>
    </div>
    <div class="page-help">
        <p>Name, address, and card fingerprint (brand + last 4) collected at checkout. Full card numbers and CVC are never stored — Stripe holds those.</p>
    </div>

    @include('admin.partials.list-toolbar', [
        'showSearch' => true,
        'searchPlaceholder' => 'Search name, email, phone, last 4, invoice…',
        'filters' => [
            [
                'name' => 'status',
                'label' => 'All statuses',
                'options' => [
                    'pending' => 'Pending',
                    'succeeded' => 'Succeeded',
                    'failed' => 'Failed',
                ],
            ],
        ],
    ])

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Paid</th>
                    <th>Invoice</th>
                    <th>Payer</th>
                    <th>Address</th>
                    <th>Card</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $payment)
                    <tr>
                        <td>{{ $payment->paid_at?->format('M d, Y H:i') ?? $payment->created_at->format('M d, Y H:i') }}</td>
                        <td>
                            @if($payment->invoice)
                                <a href="{{ route('admin.accounts.invoices.show', $payment->invoice) }}">{{ $payment->invoice->number }}</a>
                            @else
                                —
                            @endif
                        </td>
                        <td>
                            <strong>{{ $payment->name }}</strong>
                            <div class="form-meta">{{ $payment->email }} · {{ $payment->phone }}</div>
                        </td>
                        <td>{{ $payment->formattedAddress() }}</td>
                        <td>{{ $payment->cardLabel() }}</td>
                        <td>{{ $payment->currency }} {{ number_format($payment->amount, 2) }}</td>
                        <td><span class="badge {{ $payment->status === 'succeeded' ? 'badge-published' : ($payment->status === 'failed' ? 'badge-unread' : 'badge-draft') }}">{{ $payment->status }}</span></td>
                        <td><a href="{{ route('admin.system.stripe.payments.show', $payment) }}" class="btn btn-sm btn-outline">View</a></td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="empty-state">No card checkouts yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $payments->links() }}
</div>
@endsection
