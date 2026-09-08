@extends('admin.layout')

@section('title', 'Pending payments')

@section('content')
<div class="card">
    <div class="card-header">
        <h2>Pending payments</h2>
        <span class="badge badge-unread">{{ $milestones->count() }} waiting</span>
    </div>
    <div class="page-help">
        <p>Portal commission is already deducted. Record the wallet and confirm the expected net (or the slightly lower amount after a processor cut).</p>
    </div>

    @forelse($milestones as $milestone)
        <div class="settlement-block">
            <div class="settlement-block-head">
                <div>
                    <strong>{{ $milestone->title }}</strong>
                    <div class="form-meta">
                        <a href="{{ route('admin.pm.projects.show', $milestone->project) }}">{{ $milestone->project?->code }} · {{ $milestone->project?->name }}</a>
                        · {{ $milestone->project?->source?->name ?? 'No source' }}
                        @if((float) ($milestone->project?->platform_commission_percent ?? 0) > 0)
                            · {{ rtrim(rtrim(number_format((float) $milestone->project->platform_commission_percent, 2), '0'), '.') }}% portal fee
                        @endif
                        · Released {{ $milestone->released_at?->format('M d, Y H:i') }} by {{ $milestone->releasedBy?->name ?? '—' }}
                    </div>
                </div>
            </div>
            @include('admin.accounts.partials.settle-form', ['milestone' => $milestone, 'paymentAccounts' => $paymentAccounts])
        </div>
    @empty
        <p class="empty-state">Nothing waiting. When sales releases a milestone, it will show up here.</p>
    @endforelse
</div>
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
