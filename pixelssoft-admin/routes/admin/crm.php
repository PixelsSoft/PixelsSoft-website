<?php

use App\Http\Controllers\Admin\Crm\ActivityAdminController;
use App\Http\Controllers\Admin\Crm\CompanyAdminController;
use App\Http\Controllers\Admin\Crm\ContactAdminController;
use App\Http\Controllers\Admin\Crm\CrmDashboardController;
use App\Http\Controllers\Admin\Crm\CrmReportController;
use App\Http\Controllers\Admin\Crm\DealAdminController;
use App\Http\Controllers\Admin\Crm\LeadAdminController;
use Illuminate\Support\Facades\Route;

Route::prefix('crm')->name('crm.')->group(function () {
    Route::middleware('permission:crm.dashboard.view')->get('/', [CrmDashboardController::class, 'index'])->name('dashboard');

    Route::middleware('permission:crm.leads.view')->get('/leads', [LeadAdminController::class, 'index'])->name('leads.index');
    Route::middleware('permission:crm.leads.create')->group(function () {
        Route::get('/leads/create', [LeadAdminController::class, 'create'])->name('leads.create');
        Route::post('/leads', [LeadAdminController::class, 'store'])->name('leads.store');
    });
    Route::middleware('permission:crm.leads.view')->get('/leads/{lead}', [LeadAdminController::class, 'show'])->name('leads.show');
    Route::middleware('permission:crm.leads.edit')->group(function () {
        Route::get('/leads/{lead}/edit', [LeadAdminController::class, 'edit'])->name('leads.edit');
        Route::put('/leads/{lead}', [LeadAdminController::class, 'update'])->name('leads.update');
    });
    Route::middleware('permission:crm.leads.delete')->delete('/leads/{lead}', [LeadAdminController::class, 'destroy'])->name('leads.destroy');
    Route::middleware('permission:crm.leads.convert')->post('/leads/{lead}/convert', [LeadAdminController::class, 'convert'])->name('leads.convert');

    Route::middleware('permission:crm.deals.view')->group(function () {
        Route::get('/deals', [DealAdminController::class, 'index'])->name('deals.index');
        Route::get('/deals/kanban', [DealAdminController::class, 'kanban'])->name('deals.kanban');
    });
    Route::middleware('permission:crm.deals.create')->group(function () {
        Route::get('/deals/create', [DealAdminController::class, 'create'])->name('deals.create');
        Route::post('/deals', [DealAdminController::class, 'store'])->name('deals.store');
    });
    Route::middleware('permission:crm.deals.edit')->group(function () {
        Route::get('/deals/{deal}/edit', [DealAdminController::class, 'edit'])->name('deals.edit');
        Route::put('/deals/{deal}', [DealAdminController::class, 'update'])->name('deals.update');
        Route::patch('/deals/{deal}/stage', [DealAdminController::class, 'moveStage'])->name('deals.move-stage');
    });
    Route::middleware('permission:crm.deals.delete')->delete('/deals/{deal}', [DealAdminController::class, 'destroy'])->name('deals.destroy');

    Route::middleware('permission:crm.companies.view')->get('/companies', [CompanyAdminController::class, 'index'])->name('companies.index');
    Route::middleware('permission:crm.companies.create')->group(function () {
        Route::get('/companies/create', [CompanyAdminController::class, 'create'])->name('companies.create');
        Route::post('/companies', [CompanyAdminController::class, 'store'])->name('companies.store');
    });
    Route::middleware('permission:crm.companies.view')->get('/companies/{company}', [CompanyAdminController::class, 'show'])->name('companies.show');
    Route::middleware('permission:crm.companies.edit')->group(function () {
        Route::get('/companies/{company}/edit', [CompanyAdminController::class, 'edit'])->name('companies.edit');
        Route::put('/companies/{company}', [CompanyAdminController::class, 'update'])->name('companies.update');
    });
    Route::middleware('permission:crm.companies.delete')->delete('/companies/{company}', [CompanyAdminController::class, 'destroy'])->name('companies.destroy');

    Route::middleware('permission:crm.contacts.view')->get('/contacts', [ContactAdminController::class, 'index'])->name('contacts.index');
    Route::middleware('permission:crm.contacts.create')->group(function () {
        Route::get('/contacts/create', [ContactAdminController::class, 'create'])->name('contacts.create');
        Route::post('/contacts', [ContactAdminController::class, 'store'])->name('contacts.store');
    });
    Route::middleware('permission:crm.contacts.edit')->group(function () {
        Route::get('/contacts/{contact}/edit', [ContactAdminController::class, 'edit'])->name('contacts.edit');
        Route::put('/contacts/{contact}', [ContactAdminController::class, 'update'])->name('contacts.update');
    });
    Route::middleware('permission:crm.contacts.delete')->delete('/contacts/{contact}', [ContactAdminController::class, 'destroy'])->name('contacts.destroy');

    Route::middleware('permission:crm.activities.create')->post('/activities', [ActivityAdminController::class, 'store'])->name('activities.store');
    Route::middleware('permission:crm.activities.edit')->patch('/activities/{activity}/complete', [ActivityAdminController::class, 'complete'])->name('activities.complete');
    Route::middleware('permission:crm.activities.delete')->delete('/activities/{activity}', [ActivityAdminController::class, 'destroy'])->name('activities.destroy');

    Route::middleware('permission:crm.reports.view')->get('/reports', [CrmReportController::class, 'index'])->name('reports.index');
});
