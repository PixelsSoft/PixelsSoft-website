<?php

namespace App\Http\Controllers\Admin\Accounts;

use App\Http\Controllers\Controller;
use App\Models\Accounts\Expense;
use App\Models\Accounts\ExpenseCategory;
use App\Models\Pm\Project;
use App\Support\MediaStorage;
use Illuminate\Http\Request;

class ExpenseAdminController extends Controller
{
    public function index()
    {
        $query = Expense::with(['category', 'submitter', 'project'])->latest('date');

        if (!auth()->user()->can('accounts.expenses.view-all')) {
            $query->where('submitted_by', auth()->id());
        }

        $expenses = $query->paginate(20);

        return view('admin.accounts.expenses.index', compact('expenses'));
    }

    public function create()
    {
        return view('admin.accounts.expenses.form', [
            'expense' => new Expense(['date' => now(), 'status' => 'pending']),
            'categories' => ExpenseCategory::orderBy('name')->get(),
            'projects' => Project::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['submitted_by'] = auth()->id();
        $data['status'] = 'pending';

        if ($request->hasFile('receipt')) {
            $data['receipt_path'] = MediaStorage::store($request->file('receipt'));
        }

        Expense::create($data);

        return redirect()->route('admin.accounts.expenses.index')->with('success', 'Expense submitted.');
    }

    public function approve(Expense $expense)
    {
        $expense->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Expense approved.');
    }

    public function reject(Expense $expense)
    {
        $expense->update(['status' => 'rejected']);

        return back()->with('success', 'Expense rejected.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'category_id' => 'nullable|exists:acc_expense_categories,id',
            'project_id' => 'nullable|exists:pm_projects,id',
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
            'vendor' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'receipt' => 'nullable|file|max:5120',
        ]);
    }
}
