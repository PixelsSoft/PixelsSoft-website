@extends('admin.layout')

@section('title', 'Stripe')

@section('content')
<div class="card">
    <div class="card-header"><h2>Stripe keys</h2></div>
    <div class="page-help">
        <p>Paste keys from your Stripe Dashboard. Card charges go to this Stripe account. Secrets are encrypted. Leave a secret field blank to keep the current value.</p>
        <p>Webhook endpoint: <code>{{ url('/stripe/webhook') }}</code> — event <code>payment_intent.succeeded</code>.</p>
    </div>
    <form method="POST" action="{{ route('admin.system.stripe.update') }}">
        @csrf
        <div class="form-grid">
            <div class="form-group">
                <label>Publishable key *</label>
                <input type="text" name="stripe_publishable_key" value="{{ old('stripe_publishable_key', $publishable) }}" placeholder="pk_live_… or pk_test_…">
            </div>
            <div class="form-group">
                <label>Secret key</label>
                <input type="password" name="stripe_secret_key" autocomplete="new-password" placeholder="{{ $secretMasked ?: 'sk_live_… or sk_test_…' }}">
                @if($secretMasked)
                    <span class="settle-amount-hint">Saved: {{ $secretMasked }}</span>
                @endif
            </div>
            <div class="form-group">
                <label>Webhook signing secret</label>
                <input type="password" name="stripe_webhook_secret" autocomplete="new-password" placeholder="{{ $webhookMasked ?: 'whsec_…' }}">
                @if($webhookMasked)
                    <span class="settle-amount-hint">Saved: {{ $webhookMasked }}</span>
                @endif
            </div>
            <div class="form-group">
                <label>Status</label>
                <input type="text" value="{{ $configured ? 'Ready to take card payments' : 'Missing keys' }}" disabled>
            </div>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Save Stripe keys</button>
            @can('system.stripe.manage')
                <a href="{{ route('admin.system.stripe.payments') }}" class="btn btn-outline">Card payment details</a>
            @endcan
        </div>
    </form>
</div>
@endsection
