@extends('admin.layout')

@section('title', 'Pending payments')

@section('content')
<div class="page-header">
    <div class="page-header-main">
        <div class="page-kicker">Accounts</div>
        <h1>Pending payments</h1>
        <div class="page-header-meta">
            <span class="badge badge-unread">{{ $milestones->count() }} waiting</span>
        </div>
    </div>
</div>

<div class="card" style="margin-bottom:12px">
    <p class="settle-form-lead" style="margin:0">Portal commission is already deducted. Open a row to record the wallet and confirm the net that landed.</p>
</div>

@forelse($milestones as $milestone)
    <details class="settle-expand">
        <summary>
            <div class="settlement-block-head" style="width:100%;margin:0">
                <div>
                    <strong>{{ $milestone->title }}</strong>
                    <div class="form-meta">
                        <a href="{{ route('admin.pm.projects.show', $milestone->project) }}">{{ $milestone->project?->code }} · {{ $milestone->project?->name }}</a>
                        · {{ $milestone->project?->source?->name ?? 'No source' }}
                        @if((float) ($milestone->project?->platform_commission_percent ?? 0) > 0)
                            · {{ rtrim(rtrim(number_format((float) $milestone->project->platform_commission_percent, 2), '0'), '.') }}% portal
                        @endif
                        · {{ $milestone->billing_currency ?: ($milestone->project?->currency ?: 'USD') }} {{ number_format((float) $milestone->net_amount, 2) }} expected
                    </div>
                </div>
                <span class="btn btn-sm btn-outline">Settle</span>
            </div>
        </summary>
        <div class="settle-expand-body">
            @include('admin.accounts.partials.settle-form', ['milestone' => $milestone, 'paymentAccounts' => $paymentAccounts])
        </div>
    </details>
@empty
    <div class="card">
        <p class="empty-state">Nothing waiting. When sales releases a milestone, it will show up here.</p>
    </div>
@endforelse

<script>
(function () {
    document.querySelectorAll('.settle-form').forEach(function (form) {
        var wallet = form.querySelector('.js-settle-wallet');
        var currency = form.querySelector('.js-settle-currency');
        var amount = form.querySelector('.js-settle-amount');
        if (!wallet || !currency || !amount) return;

        var billing = (form.getAttribute('data-billing-currency') || 'USD').toUpperCase();
        var expectedNet = form.getAttribute('data-expected-net') || amount.value;
        var fxCap = form.getAttribute('data-fx-cap') || '';

        function applyAmountCap() {
            var currentCurrency = (currency.value || billing).toUpperCase();
            if (currentCurrency === billing) {
                amount.setAttribute('max', expectedNet);
            } else if (fxCap) {
                amount.setAttribute('max', fxCap);
            } else {
                amount.removeAttribute('max');
            }
        }

        applyAmountCap();
        currency.addEventListener('input', applyAmountCap);

        wallet.addEventListener('change', function () {
            var option = wallet.options[wallet.selectedIndex];
            var walletCurrency = (option && option.getAttribute('data-currency') || billing).toUpperCase();
            currency.value = walletCurrency;
            if (walletCurrency === billing) {
                amount.value = expectedNet;
            } else if (amount.value === expectedNet) {
                amount.value = '';
                amount.placeholder = walletCurrency + ' actually received';
            }
            applyAmountCap();
        });
    });
})();
</script>
@endsection
