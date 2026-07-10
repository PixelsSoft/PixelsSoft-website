<?php

namespace App\Http\Controllers\Admin\Accounts;

use App\Http\Controllers\Controller;
use App\Models\Accounts\Invoice;
use App\Models\Accounts\Payment;
use Illuminate\Http\Request;

class PaymentAdminController extends Controller
{
    public function index()
    {
        $payments = Payment::with('invoice.company')->latest('paid_at')->paginate(20);

        return view('admin.accounts.payments.index', compact('payments'));
    }

    public function store(Request $request, Invoice $invoice)
    {
        $data = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'method' => 'required|in:bank_transfer,cash,card,check,other',
            'reference' => 'nullable|string|max:100',
            'paid_at' => 'required|date',
        ]);

        $invoice->payments()->create($data);

        $paidTotal = $invoice->payments()->sum('amount');
        $status = $paidTotal >= $invoice->total ? 'paid' : 'partial';
        $invoice->update(['status' => $status]);

        return back()->with('success', 'Payment recorded.');
    }
}
