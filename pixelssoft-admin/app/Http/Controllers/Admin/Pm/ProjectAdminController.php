<?php

namespace App\Http\Controllers\Admin\Pm;

use App\Http\Controllers\Controller;
use App\Models\Pm\Project;
use App\Models\Pm\ProjectMember;
use App\Models\User;
use Illuminate\Http\Request;

class ProjectAdminController extends Controller
{
    public function index(Request $request)
    {
        $projects = Project::with(['company', 'manager'])
            ->withCount('tasks')
            ->when($request->filled('q'), function ($query) use ($request) {
                $q = '%' . $request->string('q') . '%';
                $query->where(function ($inner) use ($q) {
                    $inner->where('name', 'like', $q)
                        ->orWhere('code', 'like', $q)
                        ->orWhereHas('company', fn ($c) => $c->where('name', 'like', $q));
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.pm.projects.index', compact('projects'));
    }

    public function create()
    {
        return view('admin.pm.projects.form', $this->formData(new Project(['status' => 'planning', 'priority' => 'medium'])));
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
        $project->load([
            'company', 'manager', 'tasks.assignee', 'members.user',
        ]);

        return view('admin.pm.projects.show', [
            'project' => $project,
            'users' => User::where('status', 'active')->orderBy('name')->get(),
            'memberRoles' => ['developer', 'qa', 'production', 'sales', 'manager'],
        ]);
    }

    public function edit(Project $project)
    {
        return view('admin.pm.projects.form', $this->formData($project));
    }

    public function update(Request $request, Project $project)
    {
        $project->update($this->validated($request));

        return redirect()->route('admin.pm.projects.show', $project)->with('success', 'Project updated.');
    }

    public function destroy(Project $project)
    {
        $project->delete();

        return redirect()->route('admin.pm.projects.index')->with('success', 'Project deleted.');
    }

    public function addMember(Request $request, Project $project)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|in:developer,qa,production,sales,manager',
        ]);

        ProjectMember::firstOrCreate([
            'project_id' => $project->id,
            'user_id' => $data['user_id'],
            'role' => $data['role'],
        ]);

        return back()->with('success', 'Team member added.');
    }

    public function removeMember(Project $project, ProjectMember $member)
    {
        $member->delete();

        return back()->with('success', 'Team member removed.');
    }

    private function formData(Project $project): array
    {
        return [
            'project' => $project,
            'users' => User::where('status', 'active')->orderBy('name')->get(),
        ];
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'required|in:planning,active,on_hold,completed,archived',
            'priority' => 'required|in:low,medium,high,urgent',
            'start_date' => 'nullable|date',
            'due_date' => 'nullable|date',
            'budget_hours' => 'nullable|numeric|min:0',
            'manager_id' => 'nullable|exists:users,id',
            'description' => 'nullable|string',
        ]);
    }
}
