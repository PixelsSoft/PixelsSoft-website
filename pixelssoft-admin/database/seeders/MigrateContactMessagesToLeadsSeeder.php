<?php

namespace Database\Seeders;

use App\Models\ContactMessage;
use App\Models\Crm\Lead;
use App\Services\CrmLeadFromContactService;
use Illuminate\Database\Seeder;

class MigrateContactMessagesToLeadsSeeder extends Seeder
{
    public function run(): void
    {
        $service = app(CrmLeadFromContactService::class);

        ContactMessage::whereNotIn('id', Lead::whereNotNull('contact_message_id')->pluck('contact_message_id'))
            ->each(function (ContactMessage $message) use ($service) {
                try {
                    $service->createFromMessage($message);
                } catch (\Throwable) {
                    // Skip duplicates or invalid rows.
                }
            });
    }
}
