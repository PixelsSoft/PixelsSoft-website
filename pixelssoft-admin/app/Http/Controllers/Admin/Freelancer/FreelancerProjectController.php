<?php

namespace App\Http\Controllers\Admin\Freelancer;

use App\Http\Controllers\Admin\Freelancer\Concerns\EnsuresFreelancerOwnership;
use App\Http\Controllers\Controller;
use App\Jobs\Freelancer\GenerateFreelancerProposalJob;
use App\Jobs\Freelancer\SubmitFreelancerBidJob;
use App\Jobs\Freelancer\SyncFreelancerProjectsJob;
use App\Models\Freelancer\FreelancerAuditLog;
use App\Models\Freelancer\FreelancerProject;
use App\Services\Freelancer\FreelancerAccountResolver;
use App\Services\Freelancer\FreelancerBidService;
use Illuminate\Http\Request;

class FreelancerProjectController extends Controller
{
    use EnsuresFreelancerOwnership;
    public function index(Request $request, FreelancerAccountResolver $resolver)
    {
        $account = $resolver->current();
        $status = $request->string('status')->toString();

        $projects = FreelancerProject::query()
            ->when($account, fn ($q) => $q->where('freelancer_account_id', $account->id))
            ->when($status === 'qualified', fn ($q) => $q->where('automation_status', 'qualified'))
            ->when($status === 'rejected', fn ($q) => $q->where('automation_status', 'rejected'))
            ->when($request->boolean('qualified_only'), fn ($q) => $q->where('matched', true))
            ->with('strategy')
            ->latest('posted_at')
            ->paginate(25)
            ->withQueryString();

        return view('admin.freelancer.projects.index', compact('projects', 'account', 'status'));
    }

    public function qualified(Request $request, FreelancerAccountResolver $resolver)
    {
        $request->merge(['status' => 'qualified']);
        return $this->index($request, $resolver);
    }

    public function show(FreelancerProject $project)
    {
        $this->ensureProjectOwned($project);
        $project->load(['account.settings', 'strategy', 'bids', 'automationLogs', 'account.portfolioLinks.skills']);
        $selectedPortfolioLinks = $project->account->portfolioLinks()->with('skills')->whereIn('id', $project->selected_portfolio_ids)->get();

        return view('admin.freelancer.projects.show', compact('project', 'selectedPortfolioLinks'));
    }

    public function sync(FreelancerAccountResolver $resolver)
    {
        $account = $resolver->current();
        if (!$account?->is_connected) {
            return back()->with('error', 'Connect a Freelancer account first.');
        }

        SyncFreelancerProjectsJob::dispatch($account->id);
        return back()->with('success', 'Project sync queued.');
    }

    public function reject(FreelancerProject $project)
    {
        $this->ensureProjectOwned($project);
        $project->fill(['automation_status' => 'rejected', 'reject_reason' => 'Manually rejected by admin.', 'matched' => false])->save();
        FreelancerAuditLog::create(['user_id' => auth()->id(), 'freelancer_account_id' => $project->freelancer_account_id, 'action' => 'project.rejected', 'entity_type' => FreelancerProject::class, 'entity_id' => $project->id]);
        return back()->with('success', 'Project rejected.');
    }

    public function ignore(FreelancerProject $project)
    {
        $this->ensureProjectOwned($project);
        $project->fill(['automation_status' => 'ignored'])->save();
        return back()->with('success', 'Project ignored.');
    }

    public function prepare(FreelancerProject $project)
    {
        $this->ensureProjectOwned($project);
        GenerateFreelancerProposalJob::dispatchSync($project->id);
        return back()->with('success', 'Proposal prepared.');
    }

    public function regenerate(FreelancerProject $project)
    {
        $this->ensureProjectOwned($project);
        GenerateFreelancerProposalJob::dispatch($project->id);
        return back()->with('success', 'Proposal regeneration queued.');
    }

    public function updateSuggestion(Request $request, FreelancerProject $project)
    {
        $this->ensureProjectOwned($project);
        $data = $request->validate([
            'amount' => ['nullable', 'numeric', 'min:1'],
            'delivery_days' => ['nullable', 'integer', 'min:1', 'max:365'],
            'proposal' => ['nullable', 'string', 'max:10000'],
            'portfolio_ids' => ['nullable', 'array'],
            'portfolio_ids.*' => ['integer'],
        ]);

        $suggested = $project->suggested ?: [];
        foreach (['amount', 'delivery_days', 'proposal', 'portfolio_ids'] as $key) {
            if (array_key_exists($key, $data) && $data[$key] !== null) {
                $suggested[$key] = $data[$key];
            }
        }
        $project->fill(['suggested' => $suggested])->save();

        FreelancerAuditLog::create([
            'user_id' => auth()->id(),
            'freelancer_account_id' => $project->freelancer_account_id,
            'action' => 'project.suggestion_updated',
            'entity_type' => FreelancerProject::class,
            'entity_id' => $project->id,
            'new_value' => $suggested,
        ]);

        return back()->with('success', 'Suggestion updated.');
    }

    public function bidNow(FreelancerProject $project)
    {
        $this->ensureProjectOwned($project);
        $delay = (int) ($project->account?->bid_delay_seconds ?? 0);
        $project->forceFill(['bid_queued_at' => now()])->save();
        SubmitFreelancerBidJob::dispatch($project->id, true, auth()->id())->delay(now()->addSeconds(max(0, $delay)));
        $project->fill(['automation_status' => 'queued', 'bid_status' => 'queued'])->save();
        return back()->with('success', 'Bid queued for submission.');
    }

    public function approve(FreelancerProject $project)
    {
        $this->ensureProjectOwned($project);

        if (! in_array($project->bid_status, ['approval_required', 'none'], true)) {
            return back()->with('error', 'This project is not awaiting approval.');
        }

        return $this->bidNow($project);
    }

    public function simulate(FreelancerProject $project, FreelancerBidService $bids)
    {
        $this->ensureProjectOwned($project);
        $project->load(['account.settings', 'strategy']);
        $simulation = $bids->simulate($project->account, $project, true);

        return back()->with('simulation', $simulation);
    }
}
