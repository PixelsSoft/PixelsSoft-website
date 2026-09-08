<?php

namespace App\Services;

use App\Models\Accounts\Invoice;
use App\Models\Accounts\LedgerEntry;
use App\Models\Accounts\Payment;
use App\Models\Accounts\PaymentAccount;
use App\Models\Accounts\StripePayment;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Stripe\Exception\ApiErrorException;
use Stripe\PaymentIntent;
use Stripe\PaymentMethod;
use Stripe\StripeClient;

class StripeInvoicePaymentService
{
    public function amountInCents(Invoice $invoice): int
    {
        $amount = round((float) $invoice->outstanding(), 2);
        $currency = strtolower((string) $invoice->currency);
        $zeroDecimal = ['bif', 'clp', 'djf', 'gnf', 'jpy', 'kmf', 'krw', 'mga', 'pyg', 'rwf', 'ugx', 'vnd', 'vuv', 'xaf', 'xof', 'xpf'];

        return in_array($currency, $zeroDecimal, true)
            ? (int) round($amount)
            : (int) round($amount * 100);
    }

    public function createIntent(Invoice $invoice, array $payer, array $card, string $ip, ?string $userAgent, string $returnUrl): array
    {
        if ($invoice->isClosed() || $invoice->outstanding() <= 0) {
            throw ValidationException::withMessages([
                'invoice' => 'This invoice is already paid.',
            ]);
        }

        if (!StripeConfig::secretKey()) {
            throw ValidationException::withMessages([
                'stripe' => 'Add Stripe keys in Settings → Stripe so this charge can go through.',
            ]);
        }

        $stripe = StripeConfig::client();
        $currency = strtolower($invoice->currency ?: 'usd');
        $amount = $this->amountInCents($invoice);

        if ($amount < 50 && !in_array($currency, ['bif', 'clp', 'djf', 'gnf', 'jpy', 'kmf', 'krw', 'mga', 'pyg', 'rwf', 'ugx', 'vnd', 'vuv', 'xaf', 'xof', 'xpf'], true)) {
            throw ValidationException::withMessages([
                'invoice' => 'Invoice total is too small to charge on Stripe.',
            ]);
        }

        $number = preg_replace('/\D/', '', (string) ($card['card_number'] ?? ''));
        $cvc = preg_replace('/\D/', '', (string) ($card['card_cvc'] ?? ''));
        $expMonth = (int) $card['card_exp_month'];
        $expYear = (int) $card['card_exp_year'];
        unset($card);

        $billing = [
            'name' => $payer['name'],
            'email' => $payer['email'],
            'phone' => $payer['phone'],
            'address' => [
                'line1' => $payer['address_line1'],
                'line2' => $payer['address_line2'] ?? null,
                'city' => $payer['city'],
                'state' => $payer['state'],
                'postal_code' => $payer['postal_code'],
                'country' => strtoupper($payer['country']),
            ],
        ];

        try {
            $customer = $stripe->customers->create([
                ...$billing,
                'metadata' => [
                    'invoice_id' => (string) $invoice->id,
                    'invoice_number' => $invoice->number,
                ],
            ]);

            $method = $stripe->paymentMethods->create([
                'type' => 'card',
                'card' => [
                    'number' => $number,
                    'exp_month' => $expMonth,
                    'exp_year' => $expYear,
                    'cvc' => $cvc,
                ],
                'billing_details' => $billing,
            ]);
            unset($number, $cvc);

            $intent = $stripe->paymentIntents->create([
                'amount' => $amount,
                'currency' => $currency,
                'customer' => $customer->id,
                'payment_method' => $method->id,
                'confirm' => true,
                'description' => 'Invoice ' . $invoice->number,
                'receipt_email' => $payer['email'],
                'return_url' => $returnUrl,
                'metadata' => [
                    'invoice_id' => (string) $invoice->id,
                    'invoice_number' => $invoice->number,
                ],
            ]);
        } catch (ApiErrorException $e) {
            unset($number, $cvc);
            throw new \RuntimeException($e->getError()->message ?? 'Card could not be charged.');
        }

        $stripeCard = $method->card;
        $record = StripePayment::create([
            'invoice_id' => $invoice->id,
            'name' => $payer['name'],
            'email' => $payer['email'],
            'phone' => $payer['phone'],
            'address_line1' => $payer['address_line1'],
            'address_line2' => $payer['address_line2'] ?? null,
            'city' => $payer['city'],
            'state' => $payer['state'],
            'postal_code' => $payer['postal_code'],
            'country' => strtoupper($payer['country']),
            'card_last4' => $stripeCard?->last4,
            'card_exp_month' => $stripeCard?->exp_month ?? $expMonth,
            'card_exp_year' => $stripeCard?->exp_year ?? $expYear,
            'card_brand' => $stripeCard?->brand,
            'card_funding' => $stripeCard?->funding,
            'card_country' => $stripeCard?->country,
            'stripe_customer_id' => $customer->id,
            'stripe_payment_intent_id' => $intent->id,
            'stripe_payment_method_id' => $method->id,
            'amount' => $invoice->outstanding(),
            'currency' => strtoupper($invoice->currency ?: 'USD'),
            'status' => 'pending',
            'ip_address' => $ip,
            'user_agent' => $userAgent,
        ]);

        if ($intent->status === 'succeeded') {
            $this->finalizeFromIntent($intent, $stripe);

            return [
                'ok' => true,
                'redirect' => $returnUrl,
            ];
        }

        if (in_array($intent->status, ['requires_action', 'requires_source_action'], true)) {
            return [
                'ok' => false,
                'requires_action' => true,
                'client_secret' => $intent->client_secret,
                'payment_intent_id' => $intent->id,
            ];
        }

        $record->update(['status' => 'failed']);
        throw new \RuntimeException('Payment could not be completed. Status: '.$intent->status);
    }

    public function finalizeFromIntent(PaymentIntent $intent, ?StripeClient $stripe = null): StripePayment
    {
        return DB::transaction(function () use ($intent, $stripe) {
            $record = StripePayment::query()
                ->where('stripe_payment_intent_id', $intent->id)
                ->lockForUpdate()
                ->first();

            if (!$record) {
                throw new \RuntimeException('Stripe payment record not found for ' . $intent->id);
            }

            if ($record->status === 'succeeded') {
                return $record;
            }

            $invoice = Invoice::query()->whereKey($record->invoice_id)->lockForUpdate()->firstOrFail();
            $stripe = $stripe ?: StripeConfig::client();

            $paymentMethodId = is_string($intent->payment_method) ? $intent->payment_method : $intent->payment_method?->id;
            $card = null;
            if ($paymentMethodId) {
                $method = $stripe->paymentMethods->retrieve($paymentMethodId);
                $card = $method->card ?? null;
                $this->fillCardFromMethod($record, $method);
            }

            $chargeId = null;
            $latest = $intent->latest_charge;
            if (is_string($latest)) {
                $chargeId = $latest;
            } elseif (is_object($latest) && isset($latest->id)) {
                $chargeId = $latest->id;
            }

            $wallet = PaymentAccount::query()
                ->where('provider', 'stripe')
                ->where('is_active', true)
                ->orderBy('id')
                ->first();

            $payment = Payment::create([
                'invoice_id' => $invoice->id,
                'payment_account_id' => $wallet?->id,
                'project_id' => $invoice->project_id,
                'amount' => $record->amount,
                'currency' => $record->currency,
                'method' => 'card',
                'reference' => $intent->id,
                'paid_at' => now(),
            ]);

            if ($wallet) {
                LedgerEntry::create([
                    'type' => LedgerEntry::TYPE_NET_RECEIPT,
                    'amount' => $record->amount,
                    'currency' => $record->currency,
                    'occurred_at' => now(),
                    'project_id' => $invoice->project_id,
                    'payment_account_id' => $wallet->id,
                    'invoice_id' => $invoice->id,
                    'payment_id' => $payment->id,
                    'description' => 'Stripe card payment for ' . $invoice->number,
                ]);
            }

            $invoice->load('payments');
            $paidTotal = (float) $invoice->payments()->sum('amount');
            $invoice->update([
                'status' => $paidTotal >= (float) $invoice->total ? 'paid' : 'partial',
            ]);

            $record->update([
                'payment_id' => $payment->id,
                'stripe_payment_method_id' => $paymentMethodId,
                'stripe_charge_id' => $chargeId,
                'status' => 'succeeded',
                'paid_at' => now(),
                'card_brand' => $card->brand ?? $record->card_brand,
                'card_last4' => $card->last4 ?? $record->card_last4,
                'card_exp_month' => $card->exp_month ?? $record->card_exp_month,
                'card_exp_year' => $card->exp_year ?? $record->card_exp_year,
                'card_funding' => $card->funding ?? $record->card_funding,
                'card_country' => $card->country ?? $record->card_country,
            ]);

            return $record->fresh();
        });
    }

    public function markFailed(string $paymentIntentId, ?string $message = null): void
    {
        StripePayment::query()
            ->where('stripe_payment_intent_id', $paymentIntentId)
            ->where('status', 'pending')
            ->update(['status' => 'failed']);
    }

    private function fillCardFromMethod(StripePayment $record, PaymentMethod $method): void
    {
        $card = $method->card;
        if (!$card) {
            return;
        }

        $record->card_brand = $card->brand;
        $record->card_last4 = $card->last4;
        $record->card_exp_month = $card->exp_month;
        $record->card_exp_year = $card->exp_year;
        $record->card_funding = $card->funding;
        $record->card_country = $card->country;
    }
}
