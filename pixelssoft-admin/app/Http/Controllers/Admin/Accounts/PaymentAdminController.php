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
        $payments = Payment::with('invoice.company')
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
