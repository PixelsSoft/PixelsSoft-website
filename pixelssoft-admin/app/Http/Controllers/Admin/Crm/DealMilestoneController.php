<?php

namespace App\Http\Controllers\Admin\Crm;

use App\Http\Controllers\Controller;
use App\Models\Crm\Deal;
use App\Models\Pm\Milestone;
use App\Models\Pm\Project;
use App\Services\MilestoneReleaseService;
use App\Support\ScopesByOwner;
use Illuminate\Http\Request;

class DealMilestoneController extends Controller
{
    use ScopesByOwner;

    public function store(Request $request, Deal $deal)
    {
        $this->authorizeOwnedRecord($deal->owner_id, $deal->sales_person_id);
        $project = $this->projectFor($deal);

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'due_date' => 'nullable|date',
            'amount' => 'nullable|numeric|min:0',
            'status' => 'nullable|in:pending,in_progress,completed',
        ]);

        $data['project_id'] = $project->id;
        $data['sort_order'] = $project->milestones()->count();
        Milestone::create($data);

        return redirect()->route('admin.crm.deals.show', $deal)->with('success', 'Milestone added.');
    }

    public function update(Request $request, Deal $deal, Milestone $milestone)
    {
        $this->authorizeOwnedRecord($deal->owner_id, $deal->sales_person_id);
        $this->milestoneOnDeal($deal, $milestone);

        if ($milestone->isReleased()) {
            return back()->with('error', 'Released milestones cannot be edited.');
        }

        $milestone->update($request->validate([
            'title' => 'required|string|max:255',
            'due_date' => 'nullable|date',
            'amount' => 'nullable|numeric|min:0',
            'status' => 'required|in:pending,in_progress,completed',
        ]));

        return redirect()->route('admin.crm.deals.show', $deal)->with('success', 'Milestone updated.');
    }

    public function destroy(Deal $deal, Milestone $milestone)
    {
        $this->authorizeOwnedRecord($deal->owner_id, $deal->sales_person_id);
        $this->milestoneOnDeal($deal, $milestone);

        if ($milestone->isReleased()) {
            return back()->with('error', 'Released milestones cannot be deleted.');
        }

        $milestone->delete();

        return redirect()->route('admin.crm.deals.show', $deal)->with('success', 'Milestone removed.');
    }

    public function release(Request $request, Deal $deal, Milestone $milestone, MilestoneReleaseService $service)
    {
        $this->authorizeOwnedRecord($deal->owner_id, $deal->sales_person_id);
        $this->milestoneOnDeal($deal, $milestone);

        $data = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'released_at' => 'required|date',
            'portal_milestone_id' => 'nullable|string|max:150',
            'release_notes' => 'nullable|string',
        ]);

        $service->requestRelease($milestone, $data, $request->user());

        return redirect()->route('admin.crm.deals.show', $deal)
            ->with('success', 'Milestone released. Accounts has been notified to record which wallet received the payment.');
    }

    private function projectFor(Deal $deal): Project
    {
        $project = $deal->project;
        abort_unless($project, 404, 'Win this deal first so a project exists.');

        return $project;
    }

    private function milestoneOnDeal(Deal $deal, Milestone $milestone): void
    {
        abort_unless($deal->project && $milestone->project_id === $deal->project->id, 404);
    }
}
