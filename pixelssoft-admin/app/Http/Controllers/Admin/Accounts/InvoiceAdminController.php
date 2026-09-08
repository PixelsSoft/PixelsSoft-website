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
    public function index(Request $request)
    {
        $invoices = Invoice::with('company')
            ->when($request->filled('q'), function ($query) use ($request) {
                $q = '%' . $request->string('q') . '%';
                $query->where(function ($inner) use ($q) {
                    $inner->where('number', 'like', $q)
                        ->orWhereHas('company', fn ($c) => $c->where('name', 'like', $q));
                });
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $status = (string) $request->string('status');
                if ($status === 'unpaid') {
                    $query->whereIn('status', ['unpaid', 'sent', 'draft']);
                } else {
                    $query->where('status', $status);
                }
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.accounts.invoices.index', compact('invoices'));
    }

    public function create()
    {
        return view('admin.accounts.invoices.form', $this->formData(new Invoice([
            'issue_date' => now(),
            'due_date' => now()->addDays(14),
            'status' => 'unpaid',
            'currency' => 'USD',
        ])));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request, true);
        $items = $data['items'];
        unset($data['items']);
        $data['number'] = Invoice::generateNumber();
        $data['status'] = 'unpaid';
        $data['tax'] = $data['tax'] ?? 0;
        $this->applyDealLinks($data);

        $invoice = Invoice::create($data);
        $this->syncItems($invoice, $items);

        return redirect()->route('admin.accounts.invoices.show', $invoice)->with('success', 'Unpaid invoice created. Share the payment link.');
    }

    public function show(Invoice $invoice)
    {
        $invoice->ensurePublicToken();
        $invoice->load([
            'company',
            'project.source',
            'project.deal.lead.acquisitionSource',
            'project.deal.acquisitionSource',
            'deal.lead.acquisitionSource',
            'deal.acquisitionSource',
            'items',
            'payments.paymentAccount',
        ]);

        return view('admin.accounts.invoices.show', compact('invoice'));
    }

    public function edit(Invoice $invoice)
    {
        $invoice->load('items');

        return view('admin.accounts.invoices.form', $this->formData($invoice));
    }

    public function update(Request $request, Invoice $invoice)
    {
        $data = $this->validated($request, false);
        $items = $data['items'];
        unset($data['items']);
        unset($data['status']);
        $data['tax'] = $data['tax'] ?? 0;
        $this->applyDealLinks($data);

        $invoice->update($data);
        if (!$invoice->isClosed()) {
            $this->syncItems($invoice, $items);
        }

        return redirect()->route('admin.accounts.invoices.show', $invoice)->with('success', 'Invoice updated.');
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
        $invoice->load(['company', 'project.source', 'deal.lead', 'deal.acquisitionSource', 'items', 'payments.paymentAccount']);

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
            'status' => 'unpaid',
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

    private function validated(Request $request, bool $creating): array
    {
        return $request->validate([
            'company_id' => 'nullable|exists:crm_companies,id',
            'project_id' => 'nullable|exists:pm_projects,id',
            'deal_id' => ($creating ? 'required' : 'nullable') . '|exists:crm_deals,id',
            'issue_date' => 'required|date',
            'due_date' => 'nullable|date',
            'tax' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|size:3',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);
    }

    private function applyDealLinks(array &$data): void
    {
        if (empty($data['deal_id'])) {
            return;
        }

        $deal = Deal::with('project')->find($data['deal_id']);
        if (!$deal) {
            return;
        }

        if (empty($data['company_id'])) {
            $data['company_id'] = $deal->company_id;
        }
        if (empty($data['project_id']) && $deal->project) {
            $data['project_id'] = $deal->project->id;
        }
    }

    private function formData(Invoice $invoice): array
    {
        return [
            'invoice' => $invoice,
            'companies' => Company::orderBy('name')->get(),
            'deals' => Deal::with(['company', 'project'])->orderByDesc('created_at')->take(100)->get(),
        ];
    }

    private function syncItems(Invoice $invoice, array $items): void
    {
        $invoice->items()->delete();

        foreach ($items as $item) {
            $qty = (float) $item['quantity'];
            $price = (float) $item['unit_price'];
            $invoice->items()->create([
                'description' => $item['description'],
                'quantity' => $qty,
                'unit_price' => $price,
                'amount' => round($qty * $price, 2),
            ]);
        }

        $invoice->recalculateTotals();
    }
}
