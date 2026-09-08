<?php

namespace App\Providers;

use App\Models\Accounts\PaymentAccount;
use App\Models\Accounts\SalesCommission;
use App\Models\ContactMessage;
use App\Models\Crm\AcquisitionSource;
use App\Models\Pm\Milestone;
use App\Models\Pm\ProjectMember;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Route;
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

        Route::bind('source', fn ($value) => AcquisitionSource::findOrFail($value));
        Route::bind('paymentAccount', fn ($value) => PaymentAccount::findOrFail($value));
        Route::bind('commission', fn ($value) => SalesCommission::findOrFail($value));
        Route::bind('member', fn ($value) => ProjectMember::findOrFail($value));
        Route::bind('milestone', fn ($value) => Milestone::findOrFail($value));
        Route::bind('stripePayment', fn ($value) => \App\Models\Accounts\StripePayment::findOrFail($value));

        Paginator::defaultView('vendor.pagination.admin');

        $frontendUrl = env('FRONTEND_URL', 'http://localhost:3000');

        View::composer('admin.*', function ($view) use ($frontendUrl) {
            $user = auth()->user();
            $view->with('frontendUrl', $frontendUrl);
            $view->with('unreadCount', ContactMessage::where('is_read', false)->count());
            $view->with('adminNotifications', $user ? $user->unreadNotifications()->latest()->take(6)->get() : collect());
            $view->with('adminNotificationCount', $user ? $user->unreadNotifications()->count() : 0);
        });
    }
}
