<?php

namespace App\Http\Controllers\Admin\Freelancer;

use App\Http\Controllers\Admin\Freelancer\Concerns\EnsuresFreelancerOwnership;
use App\Http\Controllers\Controller;
use App\Jobs\Freelancer\SyncFreelancerSkillsJob;
use App\Models\Freelancer\FreelancerSkill;
use App\Services\Freelancer\FreelancerAccountResolver;
use Illuminate\Http\Request;

class FreelancerSkillController extends Controller
{
    use EnsuresFreelancerOwnership;
    public function index(Request $request, FreelancerAccountResolver $resolver)
    {
        $account = $resolver->firstOrCreateForUser();
        $skills = $account->skills()
            ->when($request->filled('q'), fn ($q) => $q->where('name', 'like', '%'.$request->string('q').'%'))
            ->orderBy('priority')
            ->orderBy('name')
            ->paginate(50)
            ->withQueryString();

        return view('admin.freelancer.skills.index', compact('account', 'skills'));
    }

    public function update(Request $request, FreelancerSkill $skill)
    {
        $this->ensureSkillOwned($skill);

        $data = $request->validate([
            'is_primary' => ['nullable', 'boolean'],
            'is_secondary' => ['nullable', 'boolean'],
            'automation_enabled' => ['nullable', 'boolean'],
            'priority' => ['nullable', 'integer', 'min:1', 'max:1000'],
        ]);

        $skill->fill([
            'is_primary' => $request->boolean('is_primary'),
            'is_secondary' => $request->boolean('is_secondary'),
            'automation_enabled' => $request->boolean('automation_enabled'),
            'priority' => $data['priority'] ?? $skill->priority,
        ])->save();

        return back()->with('success', 'Skill updated.');
    }

    public function destroy(FreelancerSkill $skill)
    {
        $this->ensureSkillOwned($skill);
        $skill->delete();

        return back()->with('success', 'Skill removed from automation list.');
    }

    public function sync(FreelancerAccountResolver $resolver)
    {
        $account = $resolver->current();
        if (!$account) {
            return back()->with('error', 'No account.');
        }
        SyncFreelancerSkillsJob::dispatch($account->id);

        return back()->with('success', 'Skills sync queued.');
    }
}
