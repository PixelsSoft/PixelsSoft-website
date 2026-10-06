<?php

use App\Http\Controllers\Admin\Freelancer\FreelancerAccountController;
use App\Http\Controllers\Admin\Freelancer\FreelancerAnalyticsController;
use App\Http\Controllers\Admin\Freelancer\FreelancerAutomationController;
use App\Http\Controllers\Admin\Freelancer\FreelancerBidController;
use App\Http\Controllers\Admin\Freelancer\FreelancerDashboardController;
use App\Http\Controllers\Admin\Freelancer\FreelancerLogController;
use App\Http\Controllers\Admin\Freelancer\FreelancerPortfolioController;
use App\Http\Controllers\Admin\Freelancer\FreelancerPortfolioLinkController;
use App\Http\Controllers\Admin\Freelancer\FreelancerProjectController;
use App\Http\Controllers\Admin\Freelancer\FreelancerSettingsController;
use App\Http\Controllers\Admin\Freelancer\FreelancerSkillController;
use App\Http\Controllers\Admin\Freelancer\FreelancerStrategyController;
use App\Http\Controllers\Admin\Freelancer\FreelancerTemplateController;
use Illuminate\Support\Facades\Route;

Route::prefix('freelancer')->name('freelancer.')->group(function () {
    Route::middleware('permission:freelancer.dashboard.view')->group(function () {
        Route::get('/', [FreelancerDashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard', [FreelancerDashboardController::class, 'index']);
    });

    Route::middleware('permission:freelancer.projects.view')->group(function () {
        Route::get('/projects', [FreelancerProjectController::class, 'index'])->name('projects.index');
        Route::get('/projects/qualified', [FreelancerProjectController::class, 'qualified'])->name('projects.qualified');
        Route::get('/projects/{project}', [FreelancerProjectController::class, 'show'])->name('projects.show');
    });

    Route::middleware('permission:freelancer.projects.manage')->group(function () {
        Route::post('/projects/sync', [FreelancerProjectController::class, 'sync'])->name('projects.sync');
        Route::post('/projects/{project}/reject', [FreelancerProjectController::class, 'reject'])->name('projects.reject');
        Route::post('/projects/{project}/ignore', [FreelancerProjectController::class, 'ignore'])->name('projects.ignore');
        Route::post('/projects/{project}/prepare', [FreelancerProjectController::class, 'prepare'])->name('projects.prepare');
        Route::post('/projects/{project}/regenerate', [FreelancerProjectController::class, 'regenerate'])->name('projects.regenerate');
        Route::put('/projects/{project}/suggestion', [FreelancerProjectController::class, 'updateSuggestion'])->name('projects.suggestion');
        Route::post('/projects/{project}/bid', [FreelancerProjectController::class, 'bidNow'])->name('projects.bid');
        Route::post('/projects/{project}/approve', [FreelancerProjectController::class, 'approve'])->name('projects.approve');
        Route::post('/projects/{project}/simulate', [FreelancerProjectController::class, 'simulate'])->name('projects.simulate');
    });

    Route::middleware('permission:freelancer.bids.view')->get('/bids', [FreelancerBidController::class, 'index'])->name('bids.index');
    Route::middleware('permission:freelancer.bids.manage')->post('/bids/{bid}/cancel', [FreelancerBidController::class, 'cancel'])->name('bids.cancel');

    Route::middleware('permission:freelancer.automation.manage')->group(function () {
        Route::get('/automation', [FreelancerAutomationController::class, 'index'])->name('automation.index');
        Route::put('/automation', [FreelancerAutomationController::class, 'update'])->name('automation.update');
        Route::post('/automation/pause', [FreelancerAutomationController::class, 'pause'])->name('automation.pause');
    });

    Route::middleware('permission:freelancer.strategies.manage')->group(function () {
        Route::get('/strategies', [FreelancerStrategyController::class, 'index'])->name('strategies.index');
        Route::post('/strategies', [FreelancerStrategyController::class, 'store'])->name('strategies.store');
        Route::put('/strategies/{strategy}', [FreelancerStrategyController::class, 'update'])->name('strategies.update');
        Route::delete('/strategies/{strategy}', [FreelancerStrategyController::class, 'destroy'])->name('strategies.destroy');
    });

    Route::middleware('permission:freelancer.templates.manage')->group(function () {
        Route::get('/templates', [FreelancerTemplateController::class, 'index'])->name('templates.index');
        Route::post('/templates', [FreelancerTemplateController::class, 'store'])->name('templates.store');
        Route::put('/templates/{template}', [FreelancerTemplateController::class, 'update'])->name('templates.update');
        Route::delete('/templates/{template}', [FreelancerTemplateController::class, 'destroy'])->name('templates.destroy');
    });

    Route::middleware('permission:freelancer.skills.manage')->group(function () {
        Route::get('/skills', [FreelancerSkillController::class, 'index'])->name('skills.index');
        Route::put('/skills/{skill}', [FreelancerSkillController::class, 'update'])->name('skills.update');
        Route::delete('/skills/{skill}', [FreelancerSkillController::class, 'destroy'])->name('skills.destroy');
        Route::post('/skills/sync', [FreelancerSkillController::class, 'sync'])->name('skills.sync');
    });

    Route::middleware('permission:freelancer.portfolio.manage')->group(function () {
        Route::get('/portfolio', [FreelancerPortfolioController::class, 'index'])->name('portfolio.index');
        Route::put('/portfolio/{portfolio}', [FreelancerPortfolioController::class, 'update'])->name('portfolio.update');
        Route::post('/portfolio/sync', [FreelancerPortfolioController::class, 'sync'])->name('portfolio.sync');
        Route::get('/portfolio-links', [FreelancerPortfolioLinkController::class, 'index'])->name('portfolio-links.index');
        Route::post('/portfolio-links', [FreelancerPortfolioLinkController::class, 'store'])->name('portfolio-links.store');
        Route::put('/portfolio-links/{portfolioLink}', [FreelancerPortfolioLinkController::class, 'update'])->name('portfolio-links.update');
        Route::delete('/portfolio-links/{portfolioLink}', [FreelancerPortfolioLinkController::class, 'destroy'])->name('portfolio-links.destroy');
        Route::post('/portfolio-links/{portfolioLink}/test', [FreelancerPortfolioLinkController::class, 'test'])->name('portfolio-links.test');
    });

    Route::middleware('permission:freelancer.analytics.view')->get('/analytics', [FreelancerAnalyticsController::class, 'index'])->name('analytics.index');

    Route::middleware('permission:freelancer.account.manage')->group(function () {
        Route::get('/account', [FreelancerAccountController::class, 'index'])->name('account.index');
        Route::get('/oauth/redirect', [FreelancerAccountController::class, 'redirect'])->name('oauth.redirect');
        Route::get('/oauth/callback', [FreelancerAccountController::class, 'callback'])->name('oauth.callback');
        Route::post('/account/disconnect', [FreelancerAccountController::class, 'disconnect'])->name('account.disconnect');
        Route::post('/account/test', [FreelancerAccountController::class, 'test'])->name('account.test');
        Route::post('/account/sync', [FreelancerAccountController::class, 'syncProfile'])->name('account.sync');
    });

    Route::middleware('permission:freelancer.logs.view')->get('/logs', [FreelancerLogController::class, 'index'])->name('logs.index');

    Route::middleware('permission:freelancer.settings.manage')->group(function () {
        Route::get('/settings', [FreelancerSettingsController::class, 'index'])->name('settings.index');
        Route::put('/settings', [FreelancerSettingsController::class, 'update'])->name('settings.update');
        Route::post('/settings/test-api', [FreelancerSettingsController::class, 'testApi'])->name('settings.test-api');
        Route::post('/settings/test-ai', [FreelancerSettingsController::class, 'testAi'])->name('settings.test-ai');
    });
});
