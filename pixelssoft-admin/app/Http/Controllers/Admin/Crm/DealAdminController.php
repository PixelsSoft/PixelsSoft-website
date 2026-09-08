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
use App\Services\ProjectFinanceCalculator;
use App\Services\ProjectFromDealService;
use App\Support\ScopesByOwner;
use Illuminate\Http\Request;

class DealAdminController extends Controller
{
    use ScopesByOwner;

    public function index()
    {
        return redirect()->route('admin.crm.deals.kanban');
    }

    public function kanban()
    {
        $pipeline = Pipeline::where('is_default', true)->with(['stages' => function ($q) {
            $q->with(['deals' => function ($dq) {
                $this->scopeForCurrentUser($dq->with(['company', 'owner', 'acquisitionSource', 'project'])->orderByDesc('updated_at'));
            }]);
        }])->first();

        return view('admin.crm.deals.kanban', compact('pipeline'));
    }

    public function create()
    {
        return view('admin.crm.deals.form', $this->formData(new Deal()));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['owner_id'] = $request->user()->id;
        $data['sales_person_id'] = $request->user()->id;

        Deal::create($data);

        return redirect()->route('admin.crm.deals.kanban')->with('success', 'Deal created.');
    }

    public function edit(Deal $deal)
    {
        $this->authorizeDeal($deal);

        return view('admin.crm.deals.form', $this->formData($deal));
    }

    public function show(Deal $deal)
    {
        $this->authorizeDeal($deal);

        $deal->load([
            'company', 'contact', 'lead', 'owner', 'salesperson', 'acquisitionSource', 'stage',
            'project.milestones.paymentAccount', 'project.milestones.releasedBy', 'project.milestones.invoice',
        ]);

        return view('admin.crm.deals.show', [
            'deal' => $deal,
            'preview' => app(ProjectFinanceCalculator::class),
        ]);
    }

    public function milestones()
    {
        $query = Deal::query()
            ->whereNotNull('won_at')
            ->with(['company', 'acquisitionSource', 'project.milestones']);

        $this->scopeForCurrentUser($query);

        $deals = $query->latest('won_at')->paginate(20);

        return view('admin.crm.deals.milestones', compact('deals'));
    }

    public function update(Request $request, Deal $deal)
    {
        $this->authorizeDeal($deal);
        $deal->update($this->validated($request));

        return redirect()->route('admin.crm.deals.show', $deal)->with('success', 'Deal updated.');
    }

    public function destroy(Deal $deal)
    {
        $this->authorizeDeal($deal);
        $deal->delete();

        return redirect()->route('admin.crm.deals.kanban')->with('success', 'Deal deleted.');
    }

    public function moveStage(Request $request, Deal $deal)
    {
        $this->authorizeDeal($deal);

        $data = $request->validate([
            'stage_id' => 'required|exists:crm_pipeline_stages,id',
        ]);

        $stage = PipelineStage::findOrFail($data['stage_id']);
        $updates = ['stage_id' => $stage->id];

        if (strtolower($stage->name) === 'won') {
            $deal->loadMissing('lead');
            $updates['won_at'] = now();
            $updates['lost_reason'] = null;
            $updates['sales_person_id'] = $deal->sales_person_id
                ?: $deal->lead?->owner_id
                ?: $deal->owner_id
                ?: auth()->id();

            if (!$deal->portal_url && $deal->lead?->portal_url) {
                $updates['portal_url'] = $deal->lead->portal_url;
            }
            if (!$deal->portal_contract_id && $deal->lead?->portal_contract_id) {
                $updates['portal_contract_id'] = $deal->lead->portal_contract_id;
            }

            $deal->fill($updates);

            if (!$deal->project()->exists()) {
                app(ProjectFromDealService::class)->create($deal);
            }
        } elseif (strtolower($stage->name) === 'lost') {
            $updates['won_at'] = null;
            $updates['lost_reason'] = $request->input('lost_reason', 'Marked as lost');
        } else {
            $updates['won_at'] = null;
            $updates['lost_reason'] = null;
        }

        $deal->update($updates);

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        if (strtolower($stage->name) === 'won') {
            return redirect()->route('admin.crm.deals.show', $deal)
                ->with('success', 'Deal won. Add and release milestones here.');
        }

        return back()->with('success', 'Deal stage updated.');
    }

    private function formData(Deal $deal): array
    {
        $pipeline = Pipeline::where('is_default', true)->with('stages')->first();

        return [
            'deal' => $deal,
            'pipelines' => Pipeline::with('stages')->get(),
            'pipeline' => $pipeline,
            'companies' => Company::orderBy('name')->get(),
            'contacts' => Contact::orderBy('name')->get(),
            'leads' => Lead::orderByDesc('created_at')->take(50)->get(),
            'users' => User::where('status', 'active')->orderBy('name')->get(),
            'sources' => \App\Models\Crm\AcquisitionSource::where('is_active', true)->orderBy('name')->get(),
        ];
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => 'required|string|max:255',
            'company_id' => 'nullable|exists:crm_companies,id',
            'contact_id' => 'nullable|exists:crm_contacts,id',
            'lead_id' => 'nullable|exists:crm_leads,id',
            'source_id' => 'nullable|exists:crm_acquisition_sources,id',
            'pipeline_id' => 'required|exists:crm_pipelines,id',
            'stage_id' => 'required|exists:crm_pipeline_stages,id',
            'value' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|size:3',
            'expected_close' => 'nullable|date',
            'notes' => 'nullable|string',
            'portal_contract_id' => 'nullable|string|max:150',
            'portal_url' => 'nullable|url|max:500',
        ]);
    }

    private function authorizeDeal(Deal $deal): void
    {
        $this->authorizeOwnedRecord($deal->owner_id, $deal->sales_person_id);
    }
}
