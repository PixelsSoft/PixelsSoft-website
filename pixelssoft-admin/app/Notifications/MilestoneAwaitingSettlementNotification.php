<?php

namespace App\Notifications;

use App\Models\Pm\Milestone;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class MilestoneAwaitingSettlementNotification extends Notification
{
    use Queueable;

    public function __construct(public Milestone $milestone) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $project = $this->milestone->project;
        $currency = $this->milestone->billing_currency ?: ($project?->currency ?: 'USD');
        $source = $project?->source?->name ?? 'portal';
        $gross = number_format((float) $this->milestone->amount, 2);
        $net = number_format((float) $this->milestone->net_amount, 2);

        return [
            'title' => 'Milestone released — record the payment',
            'body' => sprintf(
                '%s on %s. Released %s %s on %s, expected in wallet after commission: %s %s.',
                $this->milestone->title,
                $project?->name ?? 'a project',
                $currency,
                $gross,
                $source,
                $currency,
                $net
            ),
            'url' => route('admin.accounts.settlements.index'),
            'project_id' => $project?->id,
            'milestone_id' => $this->milestone->id,
        ];
    }
}
