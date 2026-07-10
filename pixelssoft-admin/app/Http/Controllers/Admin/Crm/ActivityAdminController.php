<?php

namespace App\Http\Controllers\Admin\Crm;

use App\Http\Controllers\Controller;
use App\Models\Crm\Activity;
use App\Models\Crm\Deal;
use App\Models\Crm\Lead;
use Illuminate\Http\Request;

class ActivityAdminController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'type' => 'required|in:call,email,meeting,note,task',
            'subject' => 'required|string|max:255',
            'body' => 'nullable|string',
            'due_at' => 'nullable|date',
            'related_type' => 'required|in:lead,deal',
            'related_id' => 'required|integer',
        ]);

        $model = $data['related_type'] === 'lead'
            ? Lead::findOrFail($data['related_id'])
            : Deal::findOrFail($data['related_id']);

        $model->activities()->create([
            'type' => $data['type'],
            'subject' => $data['subject'],
            'body' => $data['body'] ?? null,
            'due_at' => $data['due_at'] ?? null,
            'user_id' => auth()->id(),
        ]);

        return back()->with('success', 'Activity added.');
    }

    public function complete(Activity $activity)
    {
        $activity->update(['completed_at' => now()]);

        return back()->with('success', 'Activity marked complete.');
    }

    public function destroy(Activity $activity)
    {
        $activity->delete();

        return back()->with('success', 'Activity deleted.');
    }
}
