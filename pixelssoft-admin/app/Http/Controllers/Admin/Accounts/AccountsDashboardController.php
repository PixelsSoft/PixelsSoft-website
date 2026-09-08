<?php

namespace App\Http\Controllers\Admin\Accounts;

use App\Http\Controllers\Controller;
use App\Models\Accounts\Expense;
use App\Models\Accounts\Invoice;
use App\Models\Accounts\Payment;

class AccountsDashboardController extends Controller
{
    public function index()
    {
        $netThisMonth = \App\Models\Accounts\LedgerEntry::where('type', \App\Models\Accounts\LedgerEntry::TYPE_NET_RECEIPT)
            ->whereMonth('occurred_at', now()->month)
            ->whereYear('occurred_at', now()->year)
            ->sum('amount');

        return view('admin.accounts.dashboard', [
            'stats' => [
                'invoices' => Invoice::count(),
                'outstanding' => Invoice::whereIn('status', ['sent', 'partial'])->sum('total'),
                'overdue' => Invoice::where('status', 'sent')->where('due_date', '<', now())->count(),
                'paid_this_month' => Payment::whereMonth('paid_at', now()->month)->sum('amount'),
                'pending_expenses' => Expense::where('status', 'pending')->count(),
                'net_this_month' => $netThisMonth,
                'platform_fees_month' => \App\Models\Accounts\LedgerEntry::where('type', \App\Models\Accounts\LedgerEntry::TYPE_PLATFORM_COMMISSION)
                    ->whereMonth('occurred_at', now()->month)->whereYear('occurred_at', now()->year)->sum('amount'),
                'sales_owed' => \App\Models\Accounts\SalesCommission::where('status', 'accrued')->sum('amount'),
                'pending_settlements' => \App\Models\Pm\Milestone::query()
                    ->whereNotNull('released_at')
                    ->whereNull('settled_at')
                    ->whereNull('invoice_id')
                    ->count(),
            ],
            'recentInvoices' => Invoice::with('company')->latest()->take(5)->get(),
            'pendingExpenses' => Expense::with(['submitter', 'category'])->where('status', 'pending')->latest()->take(5)->get(),
            'wallets' => \App\Models\Accounts\PaymentAccount::where('is_active', true)
                ->withSum(['ledgerEntries as net_in' => fn ($q) => $q->where('type', \App\Models\Accounts\LedgerEntry::TYPE_NET_RECEIPT)], 'amount')
                ->withSum(['ledgerEntries as sales_out' => fn ($q) => $q->where('type', \App\Models\Accounts\LedgerEntry::TYPE_SALES_PAYOUT)], 'amount')
                ->orderBy('name')
                ->get(),
            'recentLedger' => \App\Models\Accounts\LedgerEntry::with(['project', 'paymentAccount'])->latest('occurred_at')->take(8)->get(),
        ]);
    }
}
