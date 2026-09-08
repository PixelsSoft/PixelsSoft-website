<?php

namespace App\Http\Controllers\Admin\Accounts;

use App\Http\Controllers\Controller;
use App\Models\Accounts\LedgerEntry;
use App\Models\Accounts\PaymentAccount;
use App\Models\Accounts\SalesCommission;
use Illuminate\Http\Request;

class SalesCommissionAdminController extends Controller
{
    public function index(Request $request)
    {
        $commissions = SalesCommission::with(['project', 'milestone', 'salesperson', 'paidFromAccount'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->latest()
            ->paginate(30)
            ->withQueryString();

        $accounts = PaymentAccount::where('is_active', true)->orderBy('name')->get();

        return view('admin.accounts.commissions.index', compact('commissions', 'accounts'));
    }

    public function pay(Request $request, SalesCommission $commission)
    {
        if ($commission->isPaid()) {
            return back()->with('error', 'Commission is already marked paid.');
        }

        $data = $request->validate([
            'paid_from_account_id' => 'required|exists:acc_payment_accounts,id',
            'paid_at' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $paidAt = \Illuminate\Support\Carbon::parse($data['paid_at']);

        $commission->update([
            'status' => 'paid',
            'paid_at' => $paidAt,
            'paid_from_account_id' => $data['paid_from_account_id'],
            'notes' => $data['notes'] ?? $commission->notes,
        ]);

        LedgerEntry::create([
            'type' => LedgerEntry::TYPE_SALES_PAYOUT,
            'amount' => $commission->amount,
            'currency' => $commission->currency,
            'occurred_at' => $paidAt,
            'project_id' => $commission->project_id,
            'milestone_id' => $commission->milestone_id,
            'payment_account_id' => $commission->paid_from_account_id,
            'sales_commission_id' => $commission->id,
            'user_id' => $commission->user_id,
            'description' => 'Sales commission paid to ' . ($commission->salesperson?->name ?? 'salesperson'),
        ]);

        return back()->with('success', 'Commission marked paid and posted to the wallet.');
    }
}
