<?php

use App\Http\Controllers\Api\BlogController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\PortfolioController;
use App\Http\Controllers\Api\SectionController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\SettingsController;
use App\Http\Controllers\Api\ShowcaseController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/blogs', [BlogController::class, 'index']);
    Route::get('/blogs/{slug}', [BlogController::class, 'show']);
    Route::get('/portfolios', [PortfolioController::class, 'index']);
    Route::get('/showcases', [ShowcaseController::class, 'index']);
    Route::get('/services', [ServiceController::class, 'index']);
    Route::get('/sections/{page}', [SectionController::class, 'show']);
    Route::get('/settings', [SettingsController::class, 'index']);
    Route::get('/settings/google', [SettingsController::class, 'google']);
    Route::post('/contact', [ContactController::class, 'store']);
});
