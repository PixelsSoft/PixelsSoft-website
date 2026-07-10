<?php

namespace App\Http\Controllers\Admin\Crm;

use App\Http\Controllers\Controller;
use App\Models\Crm\Company;
use App\Models\Crm\Contact;
use App\Models\Crm\Deal;
use App\Models\Crm\Lead;
use App\Models\Crm\Pipeline;
use App\Models\Crm\PipelineStage;
use App\Models\Pm\Project;
use App\Models\User;
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
                $this->scopeForCurrentUser($dq->with(['company', 'owner'])->orderByDesc('updated_at'));
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
        Deal::create($this->validated($request));

        return redirect()->route('admin.crm.deals.kanban')->with('success', 'Deal created.');
    }

    public function edit(Deal $deal)
    {
        return view('admin.crm.deals.form', $this->formData($deal));
    }

    public function update(Request $request, Deal $deal)
    {
        $deal->update($this->validated($request));

        return redirect()->route('admin.crm.deals.kanban')->with('success', 'Deal updated.');
    }

    public function destroy(Deal $deal)
    {
        $deal->delete();

        return redirect()->route('admin.crm.deals.kanban')->with('success', 'Deal deleted.');
    }

    public function moveStage(Request $request, Deal $deal)
    {
        $data = $request->validate([
            'stage_id' => 'required|exists:crm_pipeline_stages,id',
        ]);

        $stage = PipelineStage::findOrFail($data['stage_id']);
        $updates = ['stage_id' => $stage->id];

        if (strtolower($stage->name) === 'won') {
            $updates['won_at'] = now();
            $updates['lost_reason'] = null;

            if (!$deal->project()->exists()) {
                Project::create([
                    'name' => $deal->title,
                    'code' => Project::generateCode(),
                    'company_id' => $deal->company_id,
                    'deal_id' => $deal->id,
                    'status' => 'planning',
                    'priority' => 'medium',
                    'budget_amount' => $deal->value,
                    'manager_id' => $deal->owner_id ?? auth()->id(),
                ]);
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
        ];
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => 'required|string|max:255',
            'company_id' => 'nullable|exists:crm_companies,id',
            'contact_id' => 'nullable|exists:crm_contacts,id',
            'lead_id' => 'nullable|exists:crm_leads,id',
            'pipeline_id' => 'required|exists:crm_pipelines,id',
            'stage_id' => 'required|exists:crm_pipeline_stages,id',
            'value' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|size:3',
            'expected_close' => 'nullable|date',
            'owner_id' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
        ]);
    }
}
