<?php

namespace App\Http\Controllers\Admin\Crm;

use App\Http\Controllers\Controller;
use App\Models\Crm\Company;
use App\Models\Crm\Contact;
use App\Models\Crm\Deal;
use App\Models\Crm\Lead;
use App\Models\Crm\Pipeline;
use App\Models\Crm\PipelineStage;
use App\Models\User;
use App\Support\ScopesByOwner;
use Illuminate\Http\Request;

class LeadAdminController extends Controller
{
    use ScopesByOwner;

    public function index(Request $request)
    {
        $leads = $this->scopeForCurrentUser(Lead::with(['company', 'contact', 'owner']))
            ->when($request->filled('q'), function ($query) use ($request) {
                $q = '%' . $request->string('q') . '%';
                $query->where(function ($inner) use ($q) {
                    $inner->where('title', 'like', $q)
                        ->orWhere('status', 'like', $q)
                        ->orWhereHas('company', fn ($c) => $c->where('name', 'like', $q))
                        ->orWhereHas('contact', fn ($c) => $c->where('name', 'like', $q));
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('score'), fn ($query) => $query->where('score', $request->string('score')))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.crm.leads.index', compact('leads'));
    }

    public function create()
    {
        return view('admin.crm.leads.form', [
            'lead' => new Lead(),
            'companies' => Company::orderBy('name')->get(),
            'contacts' => Contact::orderBy('name')->get(),
            'users' => User::where('status', 'active')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['owner_id'] = $data['owner_id'] ?? auth()->id();
        Lead::create($data);

        return redirect()->route('admin.crm.leads.index')->with('success', 'Lead created.');
    }

    public function show(Lead $lead)
    {
        $lead->load(['company', 'contact', 'owner', 'deals', 'activities.user']);

        return view('admin.crm.leads.show', compact('lead'));
    }

    public function edit(Lead $lead)
    {
        return view('admin.crm.leads.form', [
            'lead' => $lead,
            'companies' => Company::orderBy('name')->get(),
            'contacts' => Contact::orderBy('name')->get(),
            'users' => User::where('status', 'active')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Lead $lead)
    {
        $lead->update($this->validated($request));

        return redirect()->route('admin.crm.leads.index')->with('success', 'Lead updated.');
    }

    public function destroy(Lead $lead)
    {
        $lead->delete();

        return redirect()->route('admin.crm.leads.index')->with('success', 'Lead deleted.');
    }

    public function convert(Lead $lead)
    {
        $pipeline = Pipeline::where('is_default', true)->first();
        $stage = $pipeline?->stages()->orderBy('order')->first();

        if (!$pipeline || !$stage) {
            return back()->with('error', 'No pipeline configured. Run database seeder.');
        }

        $deal = Deal::create([
            'title' => $lead->title,
            'company_id' => $lead->company_id,
            'contact_id' => $lead->contact_id,
            'lead_id' => $lead->id,
            'pipeline_id' => $pipeline->id,
            'stage_id' => $stage->id,
            'owner_id' => $lead->owner_id ?? auth()->id(),
            'value' => 0,
        ]);

        $lead->update(['status' => 'converted']);

        return redirect()->route('admin.crm.deals.kanban')->with('success', 'Lead converted to deal.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => 'required|string|max:255',
            'company_id' => 'nullable|exists:crm_companies,id',
            'contact_id' => 'nullable|exists:crm_contacts,id',
            'source' => 'nullable|string|max:100',
            'status' => 'required|in:new,contacted,qualified,converted,lost',
            'score' => 'required|in:hot,warm,cold',
            'owner_id' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
        ]);
    }
}
