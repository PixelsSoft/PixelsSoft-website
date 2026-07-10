<?php

namespace App\Http\Controllers\Admin\Accounts;

use App\Http\Controllers\Controller;
use App\Models\Accounts\Invoice;
use App\Models\Accounts\InvoiceItem;
use App\Models\Crm\Company;
use App\Models\Crm\Deal;
use App\Models\Pm\Project;
use App\Models\Pm\TimeEntry;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class InvoiceAdminController extends Controller
{
    public function index()
    {
        $invoices = Invoice::with('company')->latest()->paginate(20);

        return view('admin.accounts.invoices.index', compact('invoices'));
    }

    public function create()
    {
        return view('admin.accounts.invoices.form', [
            'invoice' => new Invoice(['issue_date' => now(), 'status' => 'draft', 'currency' => 'USD']),
            'companies' => Company::orderBy('name')->get(),
            'projects' => Project::orderBy('name')->get(),
            'deals' => Deal::orderByDesc('created_at')->take(50)->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['number'] = Invoice::generateNumber();
        $invoice = Invoice::create($data);

        return redirect()->route('admin.accounts.invoices.show', $invoice)->with('success', 'Invoice created.');
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['company', 'project', 'items', 'payments']);

        return view('admin.accounts.invoices.show', compact('invoice'));
    }

    public function edit(Invoice $invoice)
    {
        return view('admin.accounts.invoices.form', [
            'invoice' => $invoice,
            'companies' => Company::orderBy('name')->get(),
            'projects' => Project::orderBy('name')->get(),
            'deals' => Deal::orderByDesc('created_at')->take(50)->get(),
        ]);
    }

    public function update(Request $request, Invoice $invoice)
    {
        $invoice->update($this->validated($request));

        return redirect()->route('admin.accounts.invoices.index')->with('success', 'Invoice updated.');
    }

    public function destroy(Invoice $invoice)
    {
        $invoice->delete();

        return redirect()->route('admin.accounts.invoices.index')->with('success', 'Invoice deleted.');
    }

    public function addItem(Request $request, Invoice $invoice)
    {
        $data = $request->validate([
            'description' => 'required|string|max:255',
            'quantity' => 'required|numeric|min:0.01',
            'unit_price' => 'required|numeric|min:0',
        ]);

        $data['amount'] = $data['quantity'] * $data['unit_price'];
        $invoice->items()->create($data);
        $invoice->recalculateTotals();

        return back()->with('success', 'Line item added.');
    }

    public function removeItem(Invoice $invoice, InvoiceItem $item)
    {
        $item->delete();
        $invoice->recalculateTotals();

        return back()->with('success', 'Line item removed.');
    }

    public function pdf(Invoice $invoice)
    {
        $invoice->load(['company', 'project', 'items', 'payments']);

        return Pdf::loadView('admin.accounts.invoices.pdf', compact('invoice'))
            ->download($invoice->number . '.pdf');
    }

    public function fromTime(Request $request)
    {
        $data = $request->validate([
            'project_id' => 'required|exists:pm_projects,id',
            'company_id' => 'nullable|exists:crm_companies,id',
            'hourly_rate' => 'required|numeric|min:0',
        ]);

        $project = Project::findOrFail($data['project_id']);
        $entries = TimeEntry::where('project_id', $project->id)
            ->where('billable', true)
            ->whereNotNull('approved_at')
            ->whereDoesntHave('invoiceItem')
            ->with(['task', 'user'])
            ->get();

        if ($entries->isEmpty()) {
            return back()->with('error', 'No approved billable time entries available.');
        }

        $invoice = Invoice::create([
            'number' => Invoice::generateNumber(),
            'company_id' => $data['company_id'] ?? $project->company_id,
            'project_id' => $project->id,
            'status' => 'draft',
            'issue_date' => now(),
            'due_date' => now()->addDays(30),
            'currency' => 'USD',
        ]);

        foreach ($entries as $entry) {
            $amount = $entry->hours * $data['hourly_rate'];
            $invoice->items()->create([
                'description' => ($entry->task?->title ?? 'Project work') . ' — ' . $entry->user?->name . ' (' . $entry->date->format('M d') . ')',
                'quantity' => $entry->hours,
                'unit_price' => $data['hourly_rate'],
                'amount' => $amount,
                'time_entry_id' => $entry->id,
            ]);
        }

        $invoice->recalculateTotals();

        return redirect()->route('admin.accounts.invoices.show', $invoice)
            ->with('success', 'Invoice created from ' . $entries->count() . ' time entries.');
    }

    public function markSent(Invoice $invoice)
    {
        $invoice->update(['status' => 'sent']);

        return back()->with('success', 'Invoice marked as sent.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'company_id' => 'nullable|exists:crm_companies,id',
            'project_id' => 'nullable|exists:pm_projects,id',
            'deal_id' => 'nullable|exists:crm_deals,id',
            'status' => 'required|in:draft,sent,partial,paid,overdue,void',
            'issue_date' => 'required|date',
            'due_date' => 'nullable|date',
            'tax' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|size:3',
            'notes' => 'nullable|string',
        ]);
    }
}
