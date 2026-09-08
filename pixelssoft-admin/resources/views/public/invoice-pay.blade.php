<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Invoice {{ $invoice->number }} — PixelsSoft</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}?v={{ filemtime(public_path('css/admin.css')) }}">
    @if($publishableKey)
        <script src="https://js.stripe.com/v3/"></script>
    @endif
</head>
<body class="si-body">
@php
    $dueLabel = $invoice->due_date?->format('F j, Y');
    $amountDue = $outstanding > 0 ? $outstanding : (float) $invoice->total;
    $paid = $invoice->isClosed();
    $canPay = !$paid && $outstanding > 0;
    $code = strtoupper((string) ($invoice->currency ?: 'USD'));
    $symbols = ['USD' => '$', 'EUR' => '€', 'GBP' => '£', 'PKR' => 'Rs ', 'AED' => 'AED ', 'SAR' => 'SAR ', 'CAD' => 'CA$', 'AUD' => 'A$', 'INR' => '₹'];
    $money = fn ($n) => ($symbols[$code] ?? ($code.' ')) . number_format((float) $n, 2);
@endphp
<div class="si-shell">
    <article class="si-doc">
        <header class="si-top">
            <div>
                <h1>Invoice</h1>
                <dl class="si-meta">
                    <div><dt>Invoice number</dt><dd>{{ $invoice->number }}</dd></div>
                    @if($invoice->issue_date)
                        <div><dt>Date of issue</dt><dd>{{ $invoice->issue_date->format('F j, Y') }}</dd></div>
                    @endif
                    <div><dt>Date due</dt><dd>{{ $dueLabel ?? '—' }}</dd></div>
                </dl>
            </div>
            <img src="{{ asset('img/pixels-soft-logo.png') }}" alt="PixelsSoft" class="si-logo">
        </header>

        <div class="si-parties">
            <div>
                <div class="si-kicker">From</div>
                <strong>PixelsSoft</strong>
            </div>
            <div class="si-billto">
                <div class="si-kicker">Bill to</div>
                <strong>{{ $invoice->company?->name ?? '—' }}</strong>
                @if($invoice->company?->address)
                    <div>{{ $invoice->company->address }}</div>
                @endif
                @if($invoice->company?->phone)
                    <div>{{ $invoice->company->phone }}</div>
                @endif
            </div>
        </div>

        <div class="si-hero">
            <div class="si-hero-amount">{{ $money($amountDue) }}</div>
            <div class="si-hero-due">{{ $paid ? 'Paid' : ('due '.($dueLabel ?? 'on receipt')) }}</div>
        </div>

        <table class="si-items">
            <thead>
                <tr>
                    <th>Description</th>
                    <th>Qty</th>
                    <th>Unit price</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoice->items as $item)
                    <tr>
                        <td>{{ $item->description }}</td>
                        <td>{{ rtrim(rtrim(number_format($item->quantity, 2), '0'), '.') }}</td>
                        <td>{{ $money($item->unit_price) }}</td>
                        <td>{{ $money($item->amount) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4">No items</td></tr>
                @endforelse
            </tbody>
        </table>

        <table class="si-totals">
            <tr><td>Subtotal</td><td>{{ $money($invoice->subtotal) }}</td></tr>
            @if((float) $invoice->tax > 0)
                <tr><td>Tax</td><td>{{ $money($invoice->tax) }}</td></tr>
            @endif
            <tr><td>Total</td><td>{{ $money($invoice->total) }}</td></tr>
            <tr class="si-amount-due"><td>Amount due</td><td>{{ $money($amountDue) }}</td></tr>
        </table>
    </article>

    <aside class="si-paypanel" id="pay-section">
        @if($paid)
            <div class="si-payhead">
                <p>Invoice {{ $invoice->number }}</p>
                <h2>{{ $money($amountDue) }}</h2>
            </div>
            <div class="si-paybody">
                <div class="si-success">
                    <div class="si-success-mark">✓</div>
                    <h3>Paid</h3>
                    <p>Thank you. This invoice has been settled.</p>
                </div>
            </div>
        @elseif($outstanding <= 0)
            <div class="si-payhead">
                <p>Invoice {{ $invoice->number }}</p>
                <h2>{{ $money($amountDue) }}</h2>
            </div>
            <div class="si-paybody">
                <p class="si-muted">This invoice has no amount yet.</p>
            </div>
        @else
            <div class="si-payhead">
                <p>Pay invoice {{ $invoice->number }}</p>
                <h2>{{ $money($outstanding) }}</h2>
                @if($dueLabel)
                    <span>Due {{ $dueLabel }}</span>
                @endif
            </div>
            <div class="si-paybody">
                @unless($canCharge)
                    <p class="si-error is-visible">Card payments are not set up yet. Add keys in Settings → Stripe.</p>
                @endunless

                <form id="pay-form" class="si-form" autocomplete="on">
                    <div class="si-label">Contact</div>
                    <div class="si-stack">
                        <label class="si-float">
                            <span>Email</span>
                            <input type="email" name="email" required maxlength="150" autocomplete="email">
                        </label>
                        <div class="si-split">
                            <label class="si-float">
                                <span>Full name</span>
                                <input type="text" name="name" required maxlength="150" autocomplete="name">
                            </label>
                            <label class="si-float">
                                <span>Phone</span>
                                <input type="tel" name="phone" required maxlength="40" autocomplete="tel">
                            </label>
                        </div>
                    </div>

                    <div class="si-label">Billing address</div>
                    <div class="si-stack">
                        <label class="si-float">
                            <span>Country</span>
                            <select name="country" required autocomplete="country">
                                @foreach(['PK'=>'Pakistan','US'=>'United States','GB'=>'United Kingdom','AE'=>'United Arab Emirates','SA'=>'Saudi Arabia','CA'=>'Canada','AU'=>'Australia','IN'=>'India','DE'=>'Germany','FR'=>'France'] as $c => $label)
                                    <option value="{{ $c }}" @selected($c === 'PK')>{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="si-float">
                            <span>Address</span>
                            <input type="text" name="address_line1" required maxlength="150" autocomplete="address-line1">
                        </label>
                        <button type="button" class="si-apt-toggle" id="si-apt-toggle">+ Apt, suite, etc.</button>
                        <label class="si-float" id="si-apt" hidden>
                            <span>Apt, suite, etc.</span>
                            <input type="text" name="address_line2" maxlength="150" autocomplete="address-line2">
                        </label>
                        <div class="si-split si-split-3">
                            <label class="si-float">
                                <span>City</span>
                                <input type="text" name="city" required maxlength="100" autocomplete="address-level2">
                            </label>
                            <label class="si-float">
                                <span>State</span>
                                <input type="text" name="state" required maxlength="100" autocomplete="address-level1">
                            </label>
                            <label class="si-float">
                                <span>Postal</span>
                                <input type="text" name="postal_code" required maxlength="20" autocomplete="postal-code">
                            </label>
                        </div>
                    </div>

                    <div class="si-label si-label-card">
                        Card
                        <span class="si-brands" aria-hidden="true">
                            <svg viewBox="0 0 32 20"><rect width="32" height="20" rx="3" fill="#1a1f71"/><text x="6" y="14" fill="#fff" font-size="8" font-family="Arial" font-weight="700">VISA</text></svg>
                            <svg viewBox="0 0 32 20"><rect width="32" height="20" rx="3" fill="#f5f5f5" stroke="#e5e7eb"/><circle cx="13" cy="10" r="6" fill="#eb001b"/><circle cx="19" cy="10" r="6" fill="#f79e1b"/></svg>
                            <svg viewBox="0 0 32 20"><rect width="32" height="20" rx="3" fill="#016fd0"/><text x="5" y="14" fill="#fff" font-size="7" font-family="Arial" font-weight="700">AMEX</text></svg>
                        </span>
                    </div>
                    <div class="si-stack si-cardbox">
                        <label class="si-float">
                            <span>Card number</span>
                            <input type="text" id="card-number" inputmode="numeric" autocomplete="cc-number" maxlength="19" placeholder="ACCT-000028" required>
                        </label>
                        <div class="si-split">
                            <label class="si-float">
                                <span>Expiry</span>
                                <input type="text" id="card-exp" inputmode="numeric" autocomplete="cc-exp" maxlength="7" placeholder="MM / YY" required>
                            </label>
                            <label class="si-float">
                                <span>CVC</span>
                                <input type="text" id="card-cvc" inputmode="numeric" autocomplete="cc-csc" maxlength="4" placeholder="123" required>
                            </label>
                        </div>
                    </div>

                    <p id="pay-error" class="si-error" hidden></p>
                    <button type="submit" class="si-submit" id="pay-submit" @disabled(!$canCharge)>Pay {{ $money($outstanding) }}</button>
                    <p class="si-secure">Encrypted card payment</p>
                </form>
            </div>
        @endif
    </aside>
</div>

<script>
(function () {
    var aptToggle = document.getElementById('si-apt-toggle');
    var apt = document.getElementById('si-apt');
    if (aptToggle && apt) {
        aptToggle.addEventListener('click', function () {
            apt.hidden = false;
            aptToggle.hidden = true;
            var input = apt.querySelector('input');
            if (input) input.focus();
        });
    }
})();
</script>
@if($canPay && $canCharge)
<script>
(function () {
    var publishable = @json($publishableKey);
    var intentUrl = @json(route('public.invoice.intent', $invoice->public_token));
    var confirmUrl = @json(route('public.invoice.confirm', $invoice->public_token));
    var csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    var form = document.getElementById('pay-form');
    var errorBox = document.getElementById('pay-error');
    var submitBtn = document.getElementById('pay-submit');
    var numberInput = document.getElementById('card-number');
    var expInput = document.getElementById('card-exp');
    var cvcInput = document.getElementById('card-cvc');
    var payLabel = submitBtn.textContent;

    function showError(message) {
        errorBox.hidden = false;
        errorBox.classList.add('is-visible');
        errorBox.textContent = message;
    }

    function hideError() {
        errorBox.hidden = true;
        errorBox.classList.remove('is-visible');
        errorBox.textContent = '';
    }

    function digits(value) {
        return (value || '').replace(/\D/g, '');
    }

    function jsonError(payload, fallback) {
        if (payload && payload.message) return payload.message;
        if (payload && payload.errors) {
            var first = Object.values(payload.errors)[0];
            if (Array.isArray(first) && first[0]) return first[0];
        }
        return fallback;
    }

    numberInput.addEventListener('input', function () {
        var raw = digits(numberInput.value).slice(0, 19);
        numberInput.value = raw.replace(/(\d{4})(?=\d)/g, '$1 ').trim();
    });
    expInput.addEventListener('input', function () {
        var raw = digits(expInput.value).slice(0, 4);
        if (raw.length >= 3) raw = raw.slice(0, 2) + ' / ' + raw.slice(2);
        expInput.value = raw;
    });
    cvcInput.addEventListener('input', function () {
        cvcInput.value = digits(cvcInput.value).slice(0, 4);
    });

    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        hideError();
        if (!form.reportValidity()) return;

        var number = digits(numberInput.value);
        var exp = digits(expInput.value);
        var cvc = digits(cvcInput.value);
        var expMonth = parseInt(exp.slice(0, 2), 10);
        var expYear = parseInt(exp.slice(2), 10);
        if (expYear < 100) expYear += 2000;

        if (number.length < 13 || number.length > 19) return showError('Enter a valid card number.');
        if (!expMonth || expMonth < 1 || expMonth > 12 || !expYear) return showError('Enter a valid expiry (MM / YY).');
        if (cvc.length < 3) return showError('Enter the CVC.');

        submitBtn.disabled = true;
        submitBtn.textContent = 'Processing…';

        var data = Object.fromEntries(new FormData(form).entries());
        data.card_number = number;
        data.card_cvc = cvc;
        data.card_exp_month = expMonth;
        data.card_exp_year = expYear;

        try {
            var intentRes = await fetch(intentUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify(data)
            });
            var intentJson = await intentRes.json();
            if (!intentRes.ok) throw new Error(jsonError(intentJson, 'Could not start payment.'));

            if (intentJson.ok && intentJson.redirect) {
                window.location = intentJson.redirect;
                return;
            }

            if (intentJson.requires_action && intentJson.client_secret) {
                if (!publishable || typeof Stripe === 'undefined') {
                    throw new Error('This card needs extra verification. Add the Stripe publishable key in Settings → Stripe.');
                }
                var stripe = Stripe(publishable);
                var result = await stripe.confirmCardPayment(intentJson.client_secret);
                if (result.error) throw new Error(result.error.message);

                var confirmRes = await fetch(confirmUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                    body: JSON.stringify({ payment_intent_id: intentJson.payment_intent_id })
                });
                var confirmJson = await confirmRes.json();
                if (!confirmRes.ok || !confirmJson.ok) throw new Error(confirmJson.message || 'Could not record payment.');
                window.location = confirmJson.redirect;
                return;
            }

            throw new Error(intentJson.message || 'Payment could not be completed.');
        } catch (err) {
            showError(err.message || 'Payment failed.');
            submitBtn.disabled = false;
            submitBtn.textContent = payLabel;
        }
    });
})();
</script>
@endif
</body>
</html>
