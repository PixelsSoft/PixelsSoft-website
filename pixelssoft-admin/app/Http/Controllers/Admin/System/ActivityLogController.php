<?php

namespace App\Http\Controllers\Admin\System;

use App\Http\Controllers\Controller;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    public function index()
    {
        $activities = Activity::with('causer')->latest()->paginate(30);

        return view('admin.system.activity.index', compact('activities'));
    }

    public function export()
    {
        $activities = Activity::with('causer')->latest()->limit(5000)->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="activity-log-' . now()->format('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($activities) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['When', 'User', 'Action', 'Subject Type', 'Subject ID', 'Changes']);

            foreach ($activities as $activity) {
                fputcsv($handle, [
                    $activity->created_at->toDateTimeString(),
                    $activity->causer?->name ?? 'System',
                    $activity->description,
                    class_basename($activity->subject_type ?? ''),
                    $activity->subject_id,
                    json_encode($activity->properties['attributes'] ?? []),
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
