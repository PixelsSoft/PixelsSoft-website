<?php

namespace App\Http\Controllers\Admin\Accounts;

use App\Http\Controllers\Controller;
use App\Models\Accounts\LedgerEntry;
use App\Models\Accounts\Payment;
use App\Models\Accounts\PaymentAccount;
use Illuminate\Http\Request;

class LedgerAdminController extends Controller
{
    public function index()
    {
        $accounts = PaymentAccount::query()
            ->orderBy('name')
            ->withSum(['ledgerEntries as money_in' => fn ($q) => $q->where('type', LedgerEntry::TYPE_NET_RECEIPT)], 'amount')
            ->withSum(['ledgerEntries as money_out' => fn ($q) => $q->where('type', LedgerEntry::TYPE_SALES_PAYOUT)], 'amount')
            ->withCount(['payments as milestone_payments' => fn ($q) => $q->whereNotNull('milestone_id')])
            ->get();

        return view('admin.accounts.ledger.index', compact('accounts'));
    }

    public function show(Request $request, PaymentAccount $paymentAccount)
    {
        $entries = LedgerEntry::with(['project', 'milestone', 'user'])
            ->where('payment_account_id', $paymentAccount->id)
            ->whereIn('type', [LedgerEntry::TYPE_NET_RECEIPT, LedgerEntry::TYPE_SALES_PAYOUT])
            ->when($request->filled('q'), function ($query) use ($request) {
                $q = '%' . $request->string('q') . '%';
                $query->where(function ($inner) use ($q) {
                    $inner->where('description', 'like', $q)
                        ->orWhereHas('project', fn ($p) => $p->where('name', 'like', $q)->orWhere('code', 'like', $q))
                        ->orWhereHas('milestone', fn ($m) => $m->where('title', 'like', $q));
                });
            })
            ->orderBy('occurred_at')
            ->orderBy('id')
            ->get();

        $running = 0;
        $rows = $entries->map(function (LedgerEntry $entry) use (&$running) {
            $signed = $entry->type === LedgerEntry::TYPE_SALES_PAYOUT
                ? -1 * (float) $entry->amount
                : (float) $entry->amount;
            $running += $signed;
            $entry->signed_amount = $signed;
            $entry->running_balance = $running;

            return $entry;
        })->reverse()->values();

        $milestones = Payment::with(['milestone', 'project'])
            ->where('payment_account_id', $paymentAccount->id)
            ->whereNotNull('milestone_id')
            ->latest('paid_at')
            ->get();

        $in = (float) $entries->where('type', LedgerEntry::TYPE_NET_RECEIPT)->sum('amount');
        $out = (float) $entries->where('type', LedgerEntry::TYPE_SALES_PAYOUT)->sum('amount');

        return view('admin.accounts.ledger.show', [
            'account' => $paymentAccount,
            'rows' => $rows,
            'milestones' => $milestones,
            'moneyIn' => $in,
            'moneyOut' => $out,
            'balance' => $in - $out,
            'accounts' => PaymentAccount::orderBy('name')->get(),
        ]);
    }
}
