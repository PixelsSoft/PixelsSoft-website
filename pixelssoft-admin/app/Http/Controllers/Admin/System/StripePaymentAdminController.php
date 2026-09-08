<?php

namespace App\Http\Controllers\Admin\System;

use App\Http\Controllers\Controller;
use App\Models\Accounts\StripePayment;
use Illuminate\Http\Request;

class StripePaymentAdminController extends Controller
{
    public function index(Request $request)
    {
        $payments = StripePayment::with(['invoice.company'])
            ->when($request->filled('q'), function ($query) use ($request) {
                $q = '%' . $request->string('q') . '%';
                $query->where(function ($inner) use ($q) {
                    $inner->where('name', 'like', $q)
                        ->orWhere('email', 'like', $q)
                        ->orWhere('phone', 'like', $q)
                        ->orWhere('card_last4', 'like', $q)
                        ->orWhereHas('invoice', fn ($invoice) => $invoice->where('number', 'like', $q));
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.system.stripe.payments', compact('payments'));
    }

    public function show(StripePayment $stripePayment)
    {
        $stripePayment->load(['invoice.company', 'invoice.items', 'payment']);

        return view('admin.system.stripe.show', ['record' => $stripePayment]);
    }
}
