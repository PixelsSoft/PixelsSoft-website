<?php

namespace App\Providers;

use App\Models\ContactMessage;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Relation::morphMap([
            'lead' => \App\Models\Crm\Lead::class,
            'deal' => \App\Models\Crm\Deal::class,
        ]);

        Paginator::defaultView('vendor.pagination.admin');

        $frontendUrl = env('FRONTEND_URL', 'http://localhost:3000');

        View::composer('admin.*', function ($view) use ($frontendUrl) {
            $view->with('frontendUrl', $frontendUrl);
            $view->with('unreadCount', ContactMessage::where('is_read', false)->count());
        });
    }
}
