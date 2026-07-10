<?php

namespace App\Http\Controllers\Admin\Accounts;

use App\Http\Controllers\Controller;
use App\Models\Accounts\Expense;
use App\Models\Accounts\Invoice;
use App\Models\Accounts\Payment;
use Illuminate\Support\Facades\DB;

class AccountsReportController extends Controller
{
    public function index()
    {
        $aging = [
            'current' => Invoice::whereIn('status', ['sent', 'partial'])->where('due_date', '>=', now())->sum('total'),
            '1_30' => Invoice::whereIn('status', ['sent', 'partial', 'overdue'])
                ->whereBetween('due_date', [now()->subDays(30), now()->subDay()])->sum('total'),
            '31_60' => Invoice::whereIn('status', ['sent', 'partial', 'overdue'])
                ->whereBetween('due_date', [now()->subDays(60), now()->subDays(31)])->sum('total'),
            '61_90' => Invoice::whereIn('status', ['sent', 'partial', 'overdue'])
                ->where('due_date', '<', now()->subDays(60))->sum('total'),
        ];

        $yearExpr = DB::connection()->getDriverName() === 'sqlite'
            ? "cast(strftime('%Y', paid_at) as integer)"
            : 'YEAR(paid_at)';
        $monthExpr = DB::connection()->getDriverName() === 'sqlite'
            ? "cast(strftime('%m', paid_at) as integer)"
            : 'MONTH(paid_at)';

        return view('admin.accounts.reports.index', [
            'aging' => $aging,
            'revenueByMonth' => Payment::select(
                DB::raw("{$yearExpr} as year"),
                DB::raw("{$monthExpr} as month"),
                DB::raw('sum(amount) as total')
            )->groupBy('year', 'month')->orderByDesc('year')->orderByDesc('month')->take(12)->get(),
            'expensesByCategory' => Expense::where('status', 'approved')
                ->select('category_id', DB::raw('sum(amount) as total'))
                ->groupBy('category_id')
                ->with('category')
                ->get(),
            'totalRevenue' => Payment::sum('amount'),
            'totalExpenses' => Expense::where('status', 'approved')->sum('amount'),
            'outstanding' => Invoice::whereIn('status', ['sent', 'partial', 'overdue'])->sum('total'),
        ]);
    }
}
