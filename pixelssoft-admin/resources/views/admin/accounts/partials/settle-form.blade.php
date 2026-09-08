@php
    $project = $milestone->project;
    $billing = $milestone->billing_currency ?: ($project?->currency ?: 'USD');
    $gross = (float) $milestone->amount;
    $platformFee = (float) $milestone->platform_fee_amount;
    $net = (float) $milestone->net_amount;
    $platformPercent = (float) ($project?->platform_commission_percent ?? 0);
    if ($platformPercent <= 0 && $gross > 0 && $platformFee > 0) {
        $platformPercent = round(($platformFee / $gross) * 100, 2);
    }
    $sourceName = $project?->source?->name ?? 'Portal';
@endphp
<form method="POST" action="{{ route('admin.accounts.settlements.store', $milestone) }}" class="settle-form" data-billing-currency="{{ $billing }}" data-expected-net="{{ number_format($net, 2, '.', '') }}" data-fx-cap="{{ number_format($net * 500, 2, '.', '') }}">
    @csrf

    <div class="settlement-split">
        <div class="settlement-split-row">
            <span>Released on {{ $sourceName }}</span>
            <strong>{{ $billing }} {{ number_format($gross, 2) }}</strong>
        </div>
        @if($platformFee > 0)
            <div class="settlement-split-row settlement-split-fee">
                <span>{{ $sourceName }} commission{{ $platformPercent > 0 ? ' ('.$platformPercent.'%)' : '' }} — already applied</span>
                <strong>− {{ $billing }} {{ number_format($platformFee, 2) }}</strong>
            </div>
        @endif
        <div class="settlement-split-row settlement-split-net">
            <span>Expected in wallet</span>
            <strong>{{ $billing }} {{ number_format($net, 2) }}</strong>
        </div>
    </div>

    <p class="settle-form-lead">
        Payment is prefilled with the amount after portal commission.
        Lower it only if Wise/Payoneer took an extra cut. If the wallet is PKR, change currency and enter what actually landed.
    </p>

    <div class="form-grid">
        <div class="form-group">
            <label>Wallet *</label>
            <select name="payment_account_id" class="js-settle-wallet" required>
                <option value="">Select account</option>
                @foreach($paymentAccounts as $account)
                    <option value="{{ $account->id }}" data-currency="{{ $account->currency }}">{{ $account->name }} ({{ $account->currency }})</option>
                @endforeach
            </select>
        </div>
        <div class="form-group"><label>Payment date *</label><input type="datetime-local" name="paid_at" value="{{ now()->format('Y-m-d\\TH:i') }}" required></div>
        <div class="form-group"><label>Received currency *</label><input type="text" name="received_currency" class="js-settle-currency" maxlength="3" value="{{ $billing }}" required placeholder="{{ $billing }}"></div>
        <div class="form-group">
            <label>Amount received *</label>
            <input type="number" step="0.01" min="0.01" max="{{ number_format($net, 2, '.', '') }}" name="received_amount" class="js-settle-amount" value="{{ number_format($net, 2, '.', '') }}" required>
            <span class="settle-amount-hint">Max {{ $billing }} {{ number_format($net, 2) }} after {{ $sourceName }} commission. You can enter less if the processor took a cut.</span>
        </div>
        <div class="form-group"><label>FX rate (optional)</label><input type="number" step="0.000001" min="0" name="fx_rate" placeholder="needed only if currency ≠ {{ $billing }}"></div>
        <div class="form-group"><label>Bank / portal reference</label><input type="text" name="reference"></div>
    </div>
    <div class="form-actions" style="margin-top:12px;padding-top:0;border-top:none">
        <button type="submit" class="btn btn-primary">Record {{ $billing }} {{ number_format($net, 2) }}</button>
    </div>
</form>
