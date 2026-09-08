<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\BlogAdminController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MediaAdminController;
use App\Http\Controllers\Admin\PortfolioAdminController;
use App\Http\Controllers\Admin\ServiceAdminController;
use App\Http\Controllers\Admin\SettingsAdminController;
use App\Http\Controllers\Admin\ShowcaseAdminController;
use App\Http\Controllers\InviteController;
use App\Http\Controllers\PublicPay\InvoicePayController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('admin.login'));

Route::get('/login', [AuthController::class, 'showLogin'])->name('admin.login');
Route::post('/login', [AuthController::class, 'login'])->name('admin.login.submit');

Route::get('/invite/{token}', [InviteController::class, 'show'])->name('invite.accept');
Route::post('/invite/{token}', [InviteController::class, 'accept'])->name('invite.accept.submit');

Route::get('/pay/{token}', [InvoicePayController::class, 'show'])->name('public.invoice.pay');
Route::post('/pay/{token}/intent', [InvoicePayController::class, 'intent'])->name('public.invoice.intent');
Route::post('/pay/{token}/confirm', [InvoicePayController::class, 'confirm'])->name('public.invoice.confirm');
Route::post('/stripe/webhook', [InvoicePayController::class, 'webhook'])->name('public.stripe.webhook');

Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('blogs', BlogAdminController::class)->except(['show']);
    Route::resource('portfolios', PortfolioAdminController::class)->except(['show']);
    Route::resource('showcases', ShowcaseAdminController::class)->except(['show']);
    Route::resource('services', ServiceAdminController::class)->except(['show']);

    Route::get('/settings', [SettingsAdminController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingsAdminController::class, 'update'])->name('settings.update');
    Route::get('/settings/chat', [SettingsAdminController::class, 'chat'])->name('settings.chat');
    Route::get('/settings/google', [SettingsAdminController::class, 'google'])->name('settings.google');
    Route::post('/settings/google', [SettingsAdminController::class, 'updateGoogle'])->name('settings.google.update');

    Route::get('/sections', [SettingsAdminController::class, 'sections'])->name('sections.index');
    Route::get('/sections/{section}/edit', [SettingsAdminController::class, 'editSection'])->name('sections.edit');
    Route::put('/sections/{section}', [SettingsAdminController::class, 'updateSection'])->name('sections.update');

    Route::get('/messages', [SettingsAdminController::class, 'messages'])->name('messages.index');
    Route::patch('/messages/{message}/read', [SettingsAdminController::class, 'markMessageRead'])->name('messages.read');
    Route::delete('/messages/{message}', [SettingsAdminController::class, 'destroyMessage'])->name('messages.destroy');

    Route::get('/media', [MediaAdminController::class, 'index'])->name('media.index');
    Route::post('/media', [MediaAdminController::class, 'store'])->name('media.store');
    Route::delete('/media/{media}', [MediaAdminController::class, 'destroy'])->name('media.destroy');

    Route::get('/notifications', [\App\Http\Controllers\Admin\NotificationAdminController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read-all', [\App\Http\Controllers\Admin\NotificationAdminController::class, 'readAll'])->name('notifications.read-all');
    Route::get('/notifications/{notification}', [\App\Http\Controllers\Admin\NotificationAdminController::class, 'read'])->name('notifications.read');

    require __DIR__.'/admin/system.php';
    require __DIR__.'/admin/crm.php';
    require __DIR__.'/admin/pm.php';
    require __DIR__.'/admin/accounts.php';
    require __DIR__.'/admin/hr.php';
});
