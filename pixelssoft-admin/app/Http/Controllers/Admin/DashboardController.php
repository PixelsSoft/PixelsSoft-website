<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\ContactMessage;
use App\Models\Portfolio;
use App\Models\Service;
use App\Models\Showcase;
use Illuminate\Support\Str;

class DashboardController extends Controller
{
    public function index()
    {
        $recentMessages = ContactMessage::latest()->take(5)->get();

        return view('admin.dashboard', [
            'stats' => [
                'blogs' => Blog::count(),
                'portfolios' => Portfolio::count(),
                'showcases' => Showcase::count(),
                'services' => Service::count(),
                'unread_messages' => ContactMessage::where('is_read', false)->count(),
                'published_blogs' => Blog::where('status', 'published')->count(),
                'published_portfolios' => Portfolio::where('status', 'published')->count(),
            ],
            'recentMessages' => $recentMessages,
            'apiUrl' => url('/api/v1'),
            'frontendUrl' => env('FRONTEND_URL', 'http://localhost:3000'),
        ]);
    }

    public static function makeSlug(string $title, string $model): string
    {
        $slug = Str::slug($title);
        $original = $slug;
        $count = 1;

        while ($model::where('slug', $slug)->exists()) {
            $slug = $original . '-' . $count++;
        }

        return $slug;
    }
}
