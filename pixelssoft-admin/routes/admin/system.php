<?php

use App\Http\Controllers\Admin\System\ActivityLogController;
use App\Http\Controllers\Admin\System\InvitationAdminController;
use App\Http\Controllers\Admin\System\ProfileAdminController;
use App\Http\Controllers\Admin\System\RoleAdminController;
use App\Http\Controllers\Admin\System\UserAdminController;
use Illuminate\Support\Facades\Route;

Route::prefix('system')->name('system.')->group(function () {
    Route::middleware('permission:system.users.view')->group(function () {
        Route::get('/users', [UserAdminController::class, 'index'])->name('users.index');
    });
    Route::middleware('permission:system.users.create')->group(function () {
        Route::get('/users/create', [UserAdminController::class, 'create'])->name('users.create');
        Route::post('/users', [UserAdminController::class, 'store'])->name('users.store');
    });
    Route::middleware('permission:system.users.edit')->group(function () {
        Route::get('/users/{user}/edit', [UserAdminController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserAdminController::class, 'update'])->name('users.update');
    });
    Route::middleware('permission:system.users.delete')->delete('/users/{user}', [UserAdminController::class, 'destroy'])->name('users.destroy');

    Route::middleware('permission:system.roles.view')->get('/roles', [RoleAdminController::class, 'index'])->name('roles.index');
    Route::middleware('permission:system.roles.edit')->group(function () {
        Route::get('/roles/create', [RoleAdminController::class, 'create'])->name('roles.create');
        Route::post('/roles', [RoleAdminController::class, 'store'])->name('roles.store');
        Route::get('/roles/{role}/edit', [RoleAdminController::class, 'edit'])->name('roles.edit');
        Route::put('/roles/{role}', [RoleAdminController::class, 'update'])->name('roles.update');
    });

    Route::middleware('permission:system.invitations.manage')->group(function () {
        Route::get('/invitations', [InvitationAdminController::class, 'index'])->name('invitations.index');
        Route::post('/invitations', [InvitationAdminController::class, 'store'])->name('invitations.store');
        Route::delete('/invitations/{invitation}', [InvitationAdminController::class, 'destroy'])->name('invitations.destroy');
    });

    Route::get('/profile', [ProfileAdminController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileAdminController::class, 'update'])->name('profile.update');

    Route::middleware('permission:system.activity.view')->group(function () {
        Route::get('/activity', [ActivityLogController::class, 'index'])->name('activity.index');
        Route::get('/activity/export', [ActivityLogController::class, 'export'])->name('activity.export');
    });
});
