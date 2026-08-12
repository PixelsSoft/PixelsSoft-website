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
    public function index(Request $request)
    {
        $query = Expense::with(['category', 'submitter', 'project'])
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%' . $request->string('q') . '%';
                $q->where(function ($inner) use ($term) {
                    $inner->where('vendor', 'like', $term)
                        ->orWhere('description', 'like', $term)
                        ->orWhereHas('category', fn ($c) => $c->where('name', 'like', $term))
                        ->orWhereHas('project', fn ($p) => $p->where('name', 'like', $term))
                        ->orWhereHas('submitter', fn ($s) => $s->where('name', 'like', $term));
                });
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->latest('date');

        if (!auth()->user()->can('accounts.expenses.view-all')) {
            $query->where('submitted_by', auth()->id());
        }

        $expenses = $query->paginate(20)->withQueryString();

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
