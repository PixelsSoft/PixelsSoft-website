<?php

namespace App\Http\Controllers\Admin\Accounts;

use App\Http\Controllers\Controller;
use App\Models\Accounts\Invoice;
use App\Models\Accounts\Payment;
use Illuminate\Http\Request;

class PaymentAdminController extends Controller
{
    public function index(Request $request)
    {
        $payments = Payment::with(['invoice.company', 'paymentAccount', 'project', 'milestone'])
            ->when($request->filled('q'), function ($query) use ($request) {
                $q = '%' . $request->string('q') . '%';
                $query->where(function ($inner) use ($q) {
                    $inner->where('reference', 'like', $q)
                        ->orWhere('method', 'like', $q)
                        ->orWhereHas('invoice', function ($invoice) use ($q) {
                            $invoice->where('number', 'like', $q)
                                ->orWhereHas('company', fn ($c) => $c->where('name', 'like', $q));
                        });
                });
            })
            ->when($request->filled('method'), fn ($query) => $query->where('method', $request->string('method')))
            ->latest('paid_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.accounts.payments.index', compact('payments'));
    }

    public function store(Request $request, Invoice $invoice)
    {
        $invoice->loadMissing('payments');
        $outstanding = $invoice->outstanding();

        if ($invoice->isClosed() || $outstanding <= 0) {
            return back()->with('error', 'This invoice is already paid. You cannot add another receipt.');
        }

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01', 'max:'.$outstanding],
            'method' => 'required|in:bank_transfer,cash,card,check,other',
            'reference' => 'nullable|string|max:100',
            'paid_at' => 'required|date',
            'payment_account_id' => 'nullable|exists:acc_payment_accounts,id',
        ], [
            'amount.max' => 'Amount cannot be more than the balance due ('.$invoice->currency.' '.number_format($outstanding, 2).').',
        ]);

        $data['project_id'] = $invoice->project_id;
        $invoice->payments()->create($data);

        $paidTotal = $invoice->payments()->sum('amount');
        $status = $paidTotal >= $invoice->total ? 'paid' : 'partial';
        $invoice->update(['status' => $status]);

        return back()->with('success', 'Payment recorded.');
    }
}
