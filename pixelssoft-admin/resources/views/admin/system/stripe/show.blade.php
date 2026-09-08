@extends('admin.layout')

@section('title', 'Card payment')

@section('content')
<div class="card">
    <div class="card-header">
        <h2>{{ $record->name }}</h2>
        <a href="{{ route('admin.system.stripe.payments') }}" class="btn btn-sm btn-outline">Back</a>
    </div>
    <div class="form-grid" style="padding:0 24px 24px">
        <div><strong>Invoice:</strong>
            @if($record->invoice)
                <a href="{{ route('admin.accounts.invoices.show', $record->invoice) }}">{{ $record->invoice->number }}</a>
            @else
                —
            @endif
        </div>
        <div><strong>Status:</strong> {{ $record->status }}</div>
        <div><strong>Amount:</strong> {{ $record->currency }} {{ number_format($record->amount, 2) }}</div>
        <div><strong>Paid at:</strong> {{ $record->paid_at?->format('M d, Y H:i') ?? '—' }}</div>
        <div><strong>Name:</strong> {{ $record->name }}</div>
        <div><strong>Email:</strong> {{ $record->email }}</div>
        <div><strong>Phone:</strong> {{ $record->phone }}</div>
        <div><strong>Address:</strong> {{ $record->formattedAddress() }}</div>
        <div><strong>Card:</strong> {{ $record->cardLabel() }}</div>
        <div><strong>Card funding:</strong> {{ $record->card_funding ?: '—' }}</div>
        <div><strong>Card country:</strong> {{ $record->card_country ?: '—' }}</div>
        <div><strong>Stripe customer:</strong> {{ $record->stripe_customer_id ?: '—' }}</div>
        <div><strong>Payment intent:</strong> {{ $record->stripe_payment_intent_id ?: '—' }}</div>
        <div><strong>Payment method:</strong> {{ $record->stripe_payment_method_id ?: '—' }}</div>
        <div><strong>Charge:</strong> {{ $record->stripe_charge_id ?: '—' }}</div>
        <div><strong>IP:</strong> {{ $record->ip_address ?: '—' }}</div>
    </div>
</div>
@endsection
