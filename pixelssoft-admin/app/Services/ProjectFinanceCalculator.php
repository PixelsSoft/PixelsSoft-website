<?php

namespace App\Services;

use App\Models\Pm\Project;

class ProjectFinanceCalculator
{
    public function split(float $gross, Project $project): array
    {
        $platformPercent = (float) $project->platform_commission_percent;
        $salesPercent = (float) $project->sales_commission_percent;
        $basis = $project->sales_commission_basis === 'net' ? 'net' : 'gross';

        $platformFee = round($gross * ($platformPercent / 100), 2);
        $net = round($gross - $platformFee, 2);
        $salesBase = $basis === 'net' ? $net : $gross;
        $salesCommission = round($salesBase * ($salesPercent / 100), 2);

        return [
            'gross' => round($gross, 2),
            'platform_percent' => $platformPercent,
            'platform_fee' => $platformFee,
            'net' => $net,
            'sales_percent' => $salesPercent,
            'sales_basis' => $basis,
            'sales_commission' => $salesCommission,
            'agency_keep' => round($net - $salesCommission, 2),
        ];
    }
}
