<?php

namespace App\Http\Controllers\Admin\Pm;

use App\Http\Controllers\Controller;
use App\Models\Crm\Company;
use App\Models\Crm\Deal;
use App\Models\Pm\Project;
use App\Models\User;
use Illuminate\Http\Request;

class ProjectAdminController extends Controller
{
    public function index()
    {
        $projects = Project::with(['company', 'manager'])->withCount('tasks')->latest()->paginate(20);

        return view('admin.pm.projects.index', compact('projects'));
    }

    public function create()
    {
        return view('admin.pm.projects.form', $this->formData(new Project()));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['code'] = Project::generateCode();
        $project = Project::create($data);

        return redirect()->route('admin.pm.projects.show', $project)->with('success', 'Project created.');
    }

    public function show(Project $project)
    {
        $project->load(['company', 'deal', 'manager', 'tasks.assignee', 'milestones', 'timeEntries']);

        return view('admin.pm.projects.show', compact('project'));
    }

    public function edit(Project $project)
    {
        return view('admin.pm.projects.form', $this->formData($project));
    }

    public function update(Request $request, Project $project)
    {
        $project->update($this->validated($request));

        return redirect()->route('admin.pm.projects.index')->with('success', 'Project updated.');
    }

    public function destroy(Project $project)
    {
        $project->delete();

        return redirect()->route('admin.pm.projects.index')->with('success', 'Project deleted.');
    }

    private function formData(Project $project): array
    {
        return [
            'project' => $project,
            'companies' => Company::orderBy('name')->get(),
            'deals' => Deal::orderByDesc('created_at')->take(50)->get(),
            'users' => User::where('status', 'active')->orderBy('name')->get(),
        ];
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'company_id' => 'nullable|exists:crm_companies,id',
            'deal_id' => 'nullable|exists:crm_deals,id',
            'status' => 'required|in:planning,active,on_hold,completed,archived',
            'priority' => 'required|in:low,medium,high,urgent',
            'start_date' => 'nullable|date',
            'due_date' => 'nullable|date',
            'budget_hours' => 'nullable|numeric|min:0',
            'budget_amount' => 'nullable|numeric|min:0',
            'manager_id' => 'nullable|exists:users,id',
            'description' => 'nullable|string',
        ]);
    }
}
