<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class NotificationAdminController extends Controller
{
    public function index()
    {
        $notifications = auth()->user()->notifications()->paginate(30);

        return view('admin.notifications.index', compact('notifications'));
    }

    public function read(Request $request, DatabaseNotification $notification)
    {
        abort_unless($notification->notifiable_id === $request->user()->id, 403);
        $notification->markAsRead();

        $url = $notification->data['url'] ?? route('admin.notifications.index');

        return redirect($url);
    }

    public function readAll(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return back()->with('success', 'All notifications marked read.');
    }
}
