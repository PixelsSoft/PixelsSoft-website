<?php

namespace App\Http\Controllers\Admin\Pm;

use App\Http\Controllers\Controller;
use App\Models\Pm\Milestone;
use App\Models\Pm\Project;
use Illuminate\Http\Request;

class MilestoneAdminController extends Controller
{
    public function store(Request $request, Project $project)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'due_date' => 'nullable|date',
            'amount' => 'nullable|numeric|min:0',
            'status' => 'nullable|in:pending,in_progress,completed,released',
        ]);

        $data['project_id'] = $project->id;
        $data['sort_order'] = $project->milestones()->count();
        Milestone::create($data);

        return back()->with('success', 'Milestone added.');
    }

    public function update(Request $request, Project $project, Milestone $milestone)
    {
        if ($milestone->isReleased()) {
            return back()->with('error', 'Released milestones cannot be edited. Reverse in Accounts if a correction is needed.');
        }

        $milestone->update($request->validate([
            'title' => 'required|string|max:255',
            'due_date' => 'nullable|date',
            'amount' => 'nullable|numeric|min:0',
            'status' => 'required|in:pending,in_progress,completed,released',
        ]));

        return back()->with('success', 'Milestone updated.');
    }

    public function destroy(Project $project, Milestone $milestone)
    {
        if ($milestone->isReleased()) {
            return back()->with('error', 'Released milestones cannot be deleted.');
        }

        $milestone->delete();

        return back()->with('success', 'Milestone removed.');
    }
}
