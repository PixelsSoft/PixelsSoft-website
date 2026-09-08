<?php

use App\Http\Controllers\Admin\Pm\PmDashboardController;
use App\Http\Controllers\Admin\Pm\PmReportController;
use App\Http\Controllers\Admin\Pm\ProjectAdminController;
use App\Http\Controllers\Admin\Pm\TaskAdminController;
use App\Http\Controllers\Admin\Pm\TimeEntryAdminController;
use Illuminate\Support\Facades\Route;

Route::prefix('pm')->name('pm.')->group(function () {
    Route::middleware('permission:pm.dashboard.view')->get('/', [PmDashboardController::class, 'index'])->name('dashboard');

    Route::middleware('permission:pm.projects.view')->get('/projects', [ProjectAdminController::class, 'index'])->name('projects.index');
    Route::middleware('permission:pm.projects.create')->group(function () {
        Route::get('/projects/create', [ProjectAdminController::class, 'create'])->name('projects.create');
        Route::post('/projects', [ProjectAdminController::class, 'store'])->name('projects.store');
    });
    Route::middleware('permission:pm.projects.view')->get('/projects/{project}', [ProjectAdminController::class, 'show'])->name('projects.show');
    Route::middleware('permission:pm.projects.edit')->group(function () {
        Route::get('/projects/{project}/edit', [ProjectAdminController::class, 'edit'])->name('projects.edit');
        Route::put('/projects/{project}', [ProjectAdminController::class, 'update'])->name('projects.update');
    });
    Route::middleware('permission:pm.projects.delete')->delete('/projects/{project}', [ProjectAdminController::class, 'destroy'])->name('projects.destroy');

    Route::middleware('permission:pm.tasks.view')->get('/projects/{project}/tasks', [TaskAdminController::class, 'kanban'])->name('projects.tasks.kanban');
    Route::middleware('permission:pm.tasks.create')->group(function () {
        Route::get('/projects/{project}/tasks/create', [TaskAdminController::class, 'create'])->name('projects.tasks.create');
        Route::post('/projects/{project}/tasks', [TaskAdminController::class, 'store'])->name('projects.tasks.store');
    });
    Route::middleware('permission:pm.tasks.edit')->group(function () {
        Route::get('/projects/{project}/tasks/{task}/edit', [TaskAdminController::class, 'edit'])->name('projects.tasks.edit');
        Route::put('/projects/{project}/tasks/{task}', [TaskAdminController::class, 'update'])->name('projects.tasks.update');
        Route::patch('/projects/{project}/tasks/{task}/status', [TaskAdminController::class, 'moveStatus'])->name('projects.tasks.move-status');
        Route::post('/projects/{project}/tasks/{task}/comments', [TaskAdminController::class, 'addComment'])->name('projects.tasks.comments.store');
    });
    Route::middleware('permission:pm.tasks.delete')->delete('/projects/{project}/tasks/{task}', [TaskAdminController::class, 'destroy'])->name('projects.tasks.destroy');

    Route::middleware('role_or_permission:pm.time.view-own|pm.time.view-all')->get('/time', [TimeEntryAdminController::class, 'index'])->name('time.index');
    Route::middleware('permission:pm.time.view-own')->group(function () {
        Route::get('/time/create', [TimeEntryAdminController::class, 'create'])->name('time.create');
        Route::post('/time', [TimeEntryAdminController::class, 'store'])->name('time.store');
    });
    Route::middleware('permission:pm.time.approve')->patch('/time/{timeEntry}/approve', [TimeEntryAdminController::class, 'approve'])->name('time.approve');
    Route::middleware('permission:pm.time.view-own')->delete('/time/{timeEntry}', [TimeEntryAdminController::class, 'destroy'])->name('time.destroy');

    Route::middleware('permission:pm.members.manage')->group(function () {
        Route::post('/projects/{project}/members', [ProjectAdminController::class, 'addMember'])->name('projects.members.store');
        Route::delete('/projects/{project}/members/{member}', [ProjectAdminController::class, 'removeMember'])->name('projects.members.destroy');
    });

    Route::middleware('permission:pm.reports.view')->get('/reports', [PmReportController::class, 'index'])->name('reports.index');
});
