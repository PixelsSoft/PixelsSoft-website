<?php

namespace App\Http\Controllers\Admin\Freelancer;

use App\Http\Controllers\Admin\Freelancer\Concerns\EnsuresFreelancerOwnership;
use App\Http\Controllers\Controller;
use App\Models\Freelancer\FreelancerBidTemplate;
use App\Services\Freelancer\FreelancerAccountResolver;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FreelancerTemplateController extends Controller
{
    use EnsuresFreelancerOwnership;
    public function index(FreelancerAccountResolver $resolver)
    {
        $account = $resolver->firstOrCreateForUser();
        $templates = $account->bidTemplates()->latest()->get();

        return view('admin.freelancer.templates.index', compact('account', 'templates'));
    }

    public function store(Request $request, FreelancerAccountResolver $resolver)
    {
        $account = $resolver->firstOrCreateForUser();
        $data = $this->validated($request, $account);
        $account->bidTemplates()->create($data);

        return back()->with('success', 'Template created.');
    }

    public function update(Request $request, FreelancerBidTemplate $template)
    {
        $this->ensureTemplateOwned($template);
        $data = $this->validated($request, $template->account);
        $template->fill($data)->save();

        return back()->with('success', 'Template updated.');
    }

    public function destroy(FreelancerBidTemplate $template)
    {
        $this->ensureTemplateOwned($template);
        $template->delete();

        return back()->with('success', 'Template deleted.');
    }

    protected function validated(Request $request, $account): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'content' => ['required', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'strategy_id' => [
                'nullable',
                'integer',
                Rule::exists('freelancer_strategies', 'id')->where('freelancer_account_id', $account->id),
            ],
        ]);
        $data['is_active'] = $request->boolean('is_active', true);

        return $data;
    }
}
