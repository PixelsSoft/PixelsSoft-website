<?php

namespace Database\Seeders;

use App\Models\Crm\Pipeline;
use App\Models\Crm\PipelineStage;
use Illuminate\Database\Seeder;

class CrmSeeder extends Seeder
{
    public function run(): void
    {
        $pipeline = Pipeline::firstOrCreate(
            ['name' => 'Sales Pipeline'],
            ['is_default' => true]
        );

        $stages = [
            ['name' => 'Qualification', 'order' => 1, 'probability' => 10, 'color' => '#94a3b8'],
            ['name' => 'Proposal', 'order' => 2, 'probability' => 30, 'color' => '#60a5fa'],
            ['name' => 'Negotiation', 'order' => 3, 'probability' => 60, 'color' => '#f59e0b'],
            ['name' => 'Won', 'order' => 4, 'probability' => 100, 'color' => '#75dab4'],
            ['name' => 'Lost', 'order' => 5, 'probability' => 0, 'color' => '#ef4444'],
        ];

        foreach ($stages as $stage) {
            PipelineStage::firstOrCreate(
                ['pipeline_id' => $pipeline->id, 'name' => $stage['name']],
                $stage
            );
        }
    }
}
