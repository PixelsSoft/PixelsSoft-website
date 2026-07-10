<?php

namespace App\Services;

use App\Models\ContactMessage;
use App\Models\Crm\Activity;
use App\Models\Crm\Company;
use App\Models\Crm\Contact;
use App\Models\Crm\Lead;
use App\Models\User;

class CrmLeadFromContactService
{
    public function createFromMessage(ContactMessage $message): Lead
    {
        $company = Company::firstOrCreate(
            ['name' => $message->name . ' (Contact)'],
            ['source' => 'website', 'status' => 'active']
        );

        $contact = Contact::updateOrCreate(
            ['email' => $message->email],
            ['name' => $message->name, 'company_id' => $company->id, 'is_primary' => true]
        );

        $owner = User::role('sales-manager')->first()
            ?? User::role('sales-rep')->first()
            ?? User::role('super-admin')->first();

        $lead = Lead::create([
            'company_id' => $company->id,
            'contact_id' => $contact->id,
            'title' => $message->subject ?: 'Website inquiry from ' . $message->name,
            'source' => 'website',
            'status' => 'new',
            'score' => 'warm',
            'owner_id' => $owner?->id,
            'notes' => $message->message,
            'contact_message_id' => $message->id,
        ]);

        $lead->activities()->create([
            'type' => 'note',
            'subject' => 'Contact form submission',
            'body' => $message->message,
            'user_id' => $owner?->id ?? 1,
        ]);

        return $lead;
    }
}
