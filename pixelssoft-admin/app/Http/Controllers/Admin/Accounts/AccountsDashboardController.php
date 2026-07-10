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
        return view('admin.accounts.dashboard', [
            'stats' => [
                'invoices' => Invoice::count(),
                'outstanding' => Invoice::whereIn('status', ['sent', 'partial'])->sum('total'),
                'overdue' => Invoice::where('status', 'sent')->where('due_date', '<', now())->count(),
                'paid_this_month' => Payment::whereMonth('paid_at', now()->month)->sum('amount'),
                'pending_expenses' => Expense::where('status', 'pending')->count(),
            ],
            'recentInvoices' => Invoice::with('company')->latest()->take(5)->get(),
            'pendingExpenses' => Expense::with(['submitter', 'category'])->where('status', 'pending')->latest()->take(5)->get(),
        ]);
    }
}
