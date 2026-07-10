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
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('admin.login'));

Route::get('/login', [AuthController::class, 'showLogin'])->name('admin.login');
Route::post('/login', [AuthController::class, 'login'])->name('admin.login.submit');

Route::get('/invite/{token}', [InviteController::class, 'show'])->name('invite.accept');
Route::post('/invite/{token}', [InviteController::class, 'accept'])->name('invite.accept.submit');

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

    Route::get('/media', [MediaAdminController::class, 'index'])->name('media.index');
    Route::post('/media', [MediaAdminController::class, 'store'])->name('media.store');
    Route::delete('/media/{media}', [MediaAdminController::class, 'destroy'])->name('media.destroy');

    require __DIR__.'/admin/system.php';
    require __DIR__.'/admin/crm.php';
    require __DIR__.'/admin/pm.php';
    require __DIR__.'/admin/accounts.php';
    require __DIR__.'/admin/hr.php';
});
