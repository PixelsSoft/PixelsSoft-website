<?php

namespace App\Http\Controllers\Admin\Pm;

use App\Http\Controllers\Controller;
use App\Models\Pm\Project;
use App\Models\Pm\Task;
use App\Models\Pm\TaskComment;
use App\Models\User;
use Illuminate\Http\Request;

class TaskAdminController extends Controller
{
    public function kanban(Project $project)
    {
        $project->load(['tasks' => fn ($q) => $q->with('assignee')->orderBy('sort_order')]);

        $tasksByStatus = collect(Task::STATUSES)->mapWithKeys(fn ($status) => [
            $status => $project->tasks->where('status', $status)->values(),
        ]);

        return view('admin.pm.tasks.kanban', compact('project', 'tasksByStatus'));
    }

    public function create(Project $project)
    {
        return view('admin.pm.tasks.form', [
            'project' => $project,
            'task' => new Task(),
            'users' => User::where('status', 'active')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request, Project $project)
    {
        $data = $this->validated($request);
        $data['project_id'] = $project->id;
        Task::create($data);

        return redirect()->route('admin.pm.projects.tasks.kanban', $project)->with('success', 'Task created.');
    }

    public function edit(Project $project, Task $task)
    {
        $task->load(['comments.user']);

        return view('admin.pm.tasks.form', [
            'project' => $project,
            'task' => $task,
            'users' => User::where('status', 'active')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Project $project, Task $task)
    {
        $task->update($this->validated($request));

        return redirect()->route('admin.pm.projects.tasks.kanban', $project)->with('success', 'Task updated.');
    }

    public function destroy(Project $project, Task $task)
    {
        $task->delete();

        return redirect()->route('admin.pm.projects.tasks.kanban', $project)->with('success', 'Task deleted.');
    }

    public function moveStatus(Request $request, Project $project, Task $task)
    {
        $data = $request->validate(['status' => 'required|in:' . implode(',', Task::STATUSES)]);
        $task->update($data);

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Task moved.');
    }

    public function addComment(Request $request, Project $project, Task $task)
    {
        $data = $request->validate(['body' => 'required|string|max:2000']);
        $task->comments()->create([
            'user_id' => auth()->id(),
            'body' => $data['body'],
        ]);

        return back()->with('success', 'Comment added.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:' . implode(',', Task::STATUSES),
            'priority' => 'required|in:low,medium,high,urgent',
            'assignee_id' => 'nullable|exists:users,id',
            'due_date' => 'nullable|date',
            'estimated_hours' => 'nullable|numeric|min:0',
        ]);
    }
}
