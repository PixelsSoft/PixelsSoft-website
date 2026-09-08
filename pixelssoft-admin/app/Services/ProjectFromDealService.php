<?php

namespace App\Services;

use App\Models\Crm\AcquisitionSource;
use App\Models\Crm\Deal;
use App\Models\Pm\Project;

class ProjectFromDealService
{
    public function create(Deal $deal): Project
    {
        $deal->loadMissing(['acquisitionSource', 'lead.acquisitionSource']);
        $source = $deal->acquisitionSource ?? $deal->lead?->acquisitionSource;
        $salesPersonId = $deal->sales_person_id
            ?: $deal->lead?->owner_id
            ?: $deal->owner_id;

        $project = Project::create([
            'name' => $deal->title,
            'code' => Project::generateCode(),
            'company_id' => $deal->company_id,
            'deal_id' => $deal->id,
            'source_id' => $source?->id ?? $deal->source_id,
            'status' => 'planning',
            'priority' => 'medium',
            'budget_amount' => $deal->value,
            'manager_id' => $deal->owner_id,
            'sales_person_id' => $salesPersonId,
            'currency' => $deal->currency ?: 'USD',
            'contract_amount' => $deal->value ?? 0,
            'platform_commission_percent' => $source?->platform_commission_percent ?? 0,
            'sales_commission_percent' => $source?->default_sales_commission_percent ?? 0,
            'sales_commission_basis' => 'gross',
        ]);

        if ($salesPersonId) {
            $project->members()->firstOrCreate([
                'user_id' => $salesPersonId,
                'role' => 'sales',
            ]);
        }

        return $project;
    }

    public function applySourceDefaults(Project $project, ?AcquisitionSource $source): void
    {
        if (!$source) {
            return;
        }

        if ($project->platform_commission_percent === null || (float) $project->platform_commission_percent === 0.0) {
            $project->platform_commission_percent = $source->platform_commission_percent;
        }
        if ($project->sales_commission_percent === null || (float) $project->sales_commission_percent === 0.0) {
            $project->sales_commission_percent = $source->default_sales_commission_percent;
        }
    }
}
