<?php

namespace App\Http\Controllers\Admin\Crm;

use App\Http\Controllers\Controller;
use App\Models\Crm\AcquisitionSource;
use App\Models\Crm\Company;
use App\Models\Crm\Deal;
use App\Models\Crm\Lead;
use App\Models\Crm\Pipeline;
use App\Support\ScopesByOwner;
use Illuminate\Http\Request;

class LeadAdminController extends Controller
{
    use ScopesByOwner;

    public function index(Request $request)
    {
        $leads = $this->scopeForCurrentUser(Lead::with(['company', 'owner', 'acquisitionSource']))
            ->when($request->filled('q'), function ($query) use ($request) {
                $q = '%' . $request->string('q') . '%';
                $query->where(function ($inner) use ($q) {
                    $inner->where('title', 'like', $q)
                        ->orWhere('status', 'like', $q)
                        ->orWhereHas('company', fn ($c) => $c->where('name', 'like', $q));
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
        return view('admin.crm.leads.form', $this->formData(new Lead()));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['owner_id'] = $request->user()->id;
        if (!empty($data['source_id'])) {
            $data['source'] = AcquisitionSource::find($data['source_id'])?->name;
        }
        Lead::create($data);

        return redirect()->route('admin.crm.leads.index')->with('success', 'Lead created.');
    }

    public function show(Lead $lead)
    {
        $this->authorizeOwnedRecord($lead->owner_id);
        $lead->load(['company', 'owner', 'acquisitionSource', 'deals', 'activities.user']);

        return view('admin.crm.leads.show', compact('lead'));
    }

    public function edit(Lead $lead)
    {
        $this->authorizeOwnedRecord($lead->owner_id);

        return view('admin.crm.leads.form', $this->formData($lead));
    }

    public function update(Request $request, Lead $lead)
    {
        $this->authorizeOwnedRecord($lead->owner_id);
        $data = $this->validated($request);
        if (!empty($data['source_id'])) {
            $data['source'] = AcquisitionSource::find($data['source_id'])?->name;
        }
        $lead->update($data);

        return redirect()->route('admin.crm.leads.index')->with('success', 'Lead updated.');
    }

    public function destroy(Lead $lead)
    {
        $this->authorizeOwnedRecord($lead->owner_id);
        $lead->delete();

        return redirect()->route('admin.crm.leads.index')->with('success', 'Lead deleted.');
    }

    public function convert(Lead $lead)
    {
        $this->authorizeOwnedRecord($lead->owner_id);

        $pipeline = Pipeline::where('is_default', true)->first();
        $stage = $pipeline?->stages()->orderBy('order')->first();

        if (!$pipeline || !$stage) {
            return back()->with('error', 'No pipeline configured. Run database seeder.');
        }

        $ownerId = $lead->owner_id ?? auth()->id();

        $deal = Deal::create([
            'title' => $lead->title,
            'company_id' => $lead->company_id,
            'contact_id' => $lead->contact_id,
            'lead_id' => $lead->id,
            'source_id' => $lead->source_id,
            'portal_contract_id' => $lead->portal_contract_id,
            'portal_url' => $lead->portal_url,
            'pipeline_id' => $pipeline->id,
            'stage_id' => $stage->id,
            'owner_id' => $ownerId,
            'sales_person_id' => $ownerId,
            'value' => (float) ($lead->budget ?? 0),
            'currency' => $lead->currency ?: 'USD',
        ]);

        $lead->update(['status' => 'converted']);

        return redirect()->route('admin.crm.deals.show', $deal)->with('success', 'Lead converted to deal.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => 'required|string|max:255',
            'company_id' => 'nullable|exists:crm_companies,id',
            'source' => 'nullable|string|max:100',
            'source_id' => 'nullable|exists:crm_acquisition_sources,id',
            'status' => 'required|in:new,contacted,qualified,converted,lost',
            'score' => 'required|in:hot,warm,cold',
            'budget' => 'required|numeric|min:0',
            'currency' => 'nullable|string|size:3',
            'notes' => 'nullable|string',
            'portal_contract_id' => 'nullable|string|max:150',
            'portal_url' => 'nullable|url|max:500',
        ]);
    }

    private function formData(Lead $lead): array
    {
        return [
            'lead' => $lead,
            'companies' => Company::orderBy('name')->get(),
            'sources' => AcquisitionSource::where('is_active', true)->orderBy('name')->get(),
        ];
    }
}
