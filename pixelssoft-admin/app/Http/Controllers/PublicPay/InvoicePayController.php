<?php

namespace App\Http\Controllers\PublicPay;

use App\Http\Controllers\Controller;
use App\Models\Accounts\Invoice;
use App\Services\StripeConfig;
use App\Services\StripeInvoicePaymentService;
use Illuminate\Http\Request;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use UnexpectedValueException;

class InvoicePayController extends Controller
{
    public function show(string $token)
    {
        $invoice = $this->invoiceByToken($token);
        $invoice->load(['company', 'deal', 'items', 'payments']);

        return view('public.invoice-pay', [
            'invoice' => $invoice,
            'publishableKey' => StripeConfig::publishableKey(),
            'canCharge' => (bool) StripeConfig::secretKey(),
            'outstanding' => $invoice->outstanding(),
        ]);
    }

    public function intent(Request $request, string $token, StripeInvoicePaymentService $service)
    {
        $invoice = $this->invoiceByToken($token);

        $payer = $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|email|max:150',
            'phone' => 'required|string|max:40',
            'address_line1' => 'required|string|max:150',
            'address_line2' => 'nullable|string|max:150',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'postal_code' => 'required|string|max:20',
            'country' => 'required|string|size:2',
        ]);

        $card = $request->validate([
            'card_number' => ['required', 'string', 'regex:/^[0-9]{13,19}$/'],
            'card_cvc' => ['required', 'string', 'regex:/^[0-9]{3,4}$/'],
            'card_exp_month' => 'required|integer|min:1|max:12',
            'card_exp_year' => 'required|integer|min:2024|max:2100',
        ]);

        try {
            $result = $service->createIntent(
                $invoice,
                $payer,
                $card,
                $request->ip() ?? '',
                substr((string) $request->userAgent(), 0, 500),
                route('public.invoice.pay', $token) . '?paid=1'
            );
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json($result);
    }

    public function confirm(Request $request, string $token, StripeInvoicePaymentService $service)
    {
        $invoice = $this->invoiceByToken($token);
        $data = $request->validate([
            'payment_intent_id' => 'required|string|max:100',
        ]);

        if (!StripeConfig::secretKey()) {
            return response()->json(['ok' => false, 'message' => 'Stripe is not configured.'], 422);
        }

        try {
            $intent = StripeConfig::client()->paymentIntents->retrieve($data['payment_intent_id']);
            if ((string) ($intent->metadata['invoice_id'] ?? '') !== (string) $invoice->id) {
                return response()->json(['ok' => false, 'message' => 'Payment does not match this invoice.'], 422);
            }

            if ($intent->status !== 'succeeded') {
                return response()->json(['ok' => false, 'message' => 'Payment is not complete yet.'], 422);
            }

            $service->finalizeFromIntent($intent);
        } catch (\Throwable $e) {
            return response()->json(['ok' => false, 'message' => 'Could not record the payment.'], 500);
        }

        return response()->json([
            'ok' => true,
            'redirect' => route('public.invoice.pay', $token) . '?paid=1',
        ]);
    }

    public function webhook(Request $request, StripeInvoicePaymentService $service)
    {
        $secret = StripeConfig::webhookSecret();
        $sig = $request->header('Stripe-Signature');

        if (!$secret || !$sig) {
            return response('Webhook secret not configured', 400);
        }

        try {
            $event = Webhook::constructEvent($request->getContent(), $sig, $secret);
        } catch (UnexpectedValueException|SignatureVerificationException $e) {
            return response('Invalid signature', 400);
        }

        if ($event->type === 'payment_intent.succeeded') {
            $intent = StripeConfig::client()->paymentIntents->retrieve($event->data->object->id);
            $service->finalizeFromIntent($intent);
        }

        if ($event->type === 'payment_intent.payment_failed') {
            $service->markFailed($event->data->object->id);
        }

        return response('ok', 200);
    }

    private function invoiceByToken(string $token): Invoice
    {
        return Invoice::query()
            ->where('public_token', $token)
            ->whereNotIn('status', ['void'])
            ->firstOrFail();
    }
}
