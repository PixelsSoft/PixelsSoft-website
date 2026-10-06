<?php

use App\Console\Commands\Freelancer\CleanupFreelancerLogsCommand;
use App\Console\Commands\Freelancer\ProcessFreelancerProjectsCommand;
use App\Console\Commands\Freelancer\SyncFreelancerBidsCommand;
use App\Console\Commands\Freelancer\SyncFreelancerPortfolioCommand;
use App\Console\Commands\Freelancer\SyncFreelancerProfileCommand;
use App\Console\Commands\Freelancer\SyncFreelancerProjectsCommand;
use App\Console\Commands\Freelancer\SyncFreelancerSkillsCommand;
use App\Console\Commands\Freelancer\TestFreelancerConnectionCommand;
use App\Console\Commands\Freelancer\UpdateFreelancerAnalyticsCommand;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
| Freelancer automation schedule
| Server cron (required): * * * * * php /path/to/artisan schedule:run
*/
Schedule::command(SyncFreelancerProjectsCommand::class)
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->name('freelancer-sync-projects');

Schedule::command(ProcessFreelancerProjectsCommand::class)
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->name('freelancer-process-projects');

Schedule::command(SyncFreelancerProfileCommand::class)
    ->hourly()
    ->withoutOverlapping()
    ->name('freelancer-sync-profile');

Schedule::command(SyncFreelancerSkillsCommand::class)
    ->dailyAt('02:00')
    ->withoutOverlapping()
    ->name('freelancer-sync-skills');

Schedule::command(SyncFreelancerPortfolioCommand::class)
    ->dailyAt('02:15')
    ->withoutOverlapping()
    ->name('freelancer-sync-portfolio');

Schedule::command(SyncFreelancerBidsCommand::class)
    ->everyThirtyMinutes()
    ->withoutOverlapping()
    ->name('freelancer-sync-bids');

Schedule::command(CleanupFreelancerLogsCommand::class)
    ->dailyAt('03:00')
    ->name('freelancer-cleanup-logs');

Schedule::command(UpdateFreelancerAnalyticsCommand::class)
    ->hourly()
    ->name('freelancer-update-analytics');
