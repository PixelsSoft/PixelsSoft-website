<?php

namespace App\Http\Controllers\Admin\System;

use App\Http\Controllers\Controller;
use App\Services\StripeConfig;
use Illuminate\Http\Request;

class StripeSettingsAdminController extends Controller
{
    public function edit()
    {
        return view('admin.system.stripe.settings', [
            'publishable' => StripeConfig::publishableKey() ?? '',
            'secretMasked' => StripeConfig::maskedSecret(),
            'webhookMasked' => StripeConfig::maskedWebhook(),
            'configured' => StripeConfig::isConfigured(),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'stripe_publishable_key' => 'nullable|string|max:255',
            'stripe_secret_key' => 'nullable|string|max:255',
            'stripe_webhook_secret' => 'nullable|string|max:255',
        ]);

        StripeConfig::setPublishable($data['stripe_publishable_key'] ?? null);
        StripeConfig::setSecret($data['stripe_secret_key'] ?? null);
        StripeConfig::setWebhookSecret($data['stripe_webhook_secret'] ?? null);

        return back()->with('success', 'Stripe keys saved. Payments will go to this Stripe account.');
    }
}
