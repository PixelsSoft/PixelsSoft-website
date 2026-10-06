<?php

namespace App\Console\Commands\Freelancer;

use App\Jobs\Freelancer\GenerateFreelancerProposalJob;
use App\Jobs\Freelancer\ProcessFreelancerProjectJob;
use App\Models\Freelancer\FreelancerProject;
use Illuminate\Console\Command;

class ProcessFreelancerProjectsCommand extends Command
{
    protected $signature = 'freelancer:process-projects {--limit=50}';

    protected $description = 'Queue processing for qualified Freelancer projects';

    public function handle(): int
    {
        $projects = FreelancerProject::query()
            ->where('automation_status', 'qualified')
            ->where('bid_status', 'none')
            ->latest('posted_at')
            ->limit((int) $this->option('limit'))
            ->get(['id', 'matching_completed_at']);

        foreach ($projects as $project) {
            if ($project->matching_completed_at) {
                GenerateFreelancerProposalJob::dispatch($project->id);
            } else {
                ProcessFreelancerProjectJob::dispatch($project->id);
            }
        }

        $this->info('Queued '.$projects->count().' projects for processing.');

        return self::SUCCESS;
    }
}
