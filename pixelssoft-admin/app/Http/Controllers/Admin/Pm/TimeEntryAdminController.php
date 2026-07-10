<?php

namespace App\Http\Controllers\Admin\Pm;

use App\Http\Controllers\Controller;
use App\Models\Pm\Project;
use App\Models\Pm\Task;
use App\Models\Pm\TimeEntry;
use Illuminate\Http\Request;

class TimeEntryAdminController extends Controller
{
    public function index()
    {
        $query = TimeEntry::with(['project', 'task', 'user'])->latest('date');

        if (auth()->user()->can('pm.time.view-all')) {
            $entries = $query->paginate(30);
        } else {
            $entries = $query->where('user_id', auth()->id())->paginate(30);
        }

        return view('admin.pm.time.index', compact('entries'));
    }

    public function create()
    {
        return view('admin.pm.time.form', [
            'entry' => new TimeEntry(),
            'projects' => Project::orderBy('name')->get(),
            'tasks' => Task::orderBy('title')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['user_id'] = auth()->id();
        TimeEntry::create($data);

        return redirect()->route('admin.pm.time.index')->with('success', 'Time entry logged.');
    }

    public function approve(TimeEntry $timeEntry)
    {
        $timeEntry->update([
            'approved_at' => now(),
            'approved_by' => auth()->id(),
        ]);

        return back()->with('success', 'Time entry approved.');
    }

    public function destroy(TimeEntry $timeEntry)
    {
        if ($timeEntry->user_id !== auth()->id() && !auth()->user()->can('pm.time.view-all')) {
            abort(403);
        }

        $timeEntry->delete();

        return back()->with('success', 'Time entry deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'project_id' => 'required|exists:pm_projects,id',
            'task_id' => 'nullable|exists:pm_tasks,id',
            'date' => 'required|date',
            'hours' => 'required|numeric|min:0.25|max:24',
            'description' => 'nullable|string|max:500',
            'billable' => 'nullable|boolean',
        ]);
    }
}
