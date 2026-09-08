<?php

namespace Database\Seeders;

use App\Models\Accounts\PaymentAccount;
use App\Models\Crm\AcquisitionSource;
use Illuminate\Database\Seeder;

class AgencyFinanceSeeder extends Seeder
{
    public function run(): void
    {
        $sources = [
            [
                'name' => 'Upwork',
                'slug' => 'upwork',
                'type' => 'freelance_portal',
                'platform_commission_percent' => 10,
                'default_sales_commission_percent' => 5,
                'notes' => 'Typical Upwork fee is around 10% and can be tiered. Adjust per contract.',
            ],
            [
                'name' => 'Freelancer.com',
                'slug' => 'freelancer-com',
                'type' => 'freelance_portal',
                'platform_commission_percent' => 10,
                'default_sales_commission_percent' => 5,
                'notes' => 'Freelancer.com takes a platform fee on milestone release. Confirm current plan %.',
            ],
            [
                'name' => 'Direct / Website',
                'slug' => 'direct',
                'type' => 'direct',
                'platform_commission_percent' => 0,
                'default_sales_commission_percent' => 8,
                'notes' => 'No portal fee. Sales commission can be higher because the deal is fully in-house.',
            ],
            [
                'name' => 'Referral',
                'slug' => 'referral',
                'type' => 'referral',
                'platform_commission_percent' => 0,
                'default_sales_commission_percent' => 5,
                'notes' => 'Use notes on the project if a referrer is paid separately.',
            ],
            [
                'name' => 'Other',
                'slug' => 'other',
                'type' => 'other',
                'platform_commission_percent' => 0,
                'default_sales_commission_percent' => 5,
            ],
        ];

        foreach ($sources as $source) {
            AcquisitionSource::updateOrCreate(['slug' => $source['slug']], $source + ['is_active' => true]);
        }

        $accounts = [
            ['name' => 'Wise', 'slug' => 'wise', 'provider' => 'wise', 'currency' => 'USD'],
            ['name' => 'Payoneer', 'slug' => 'payoneer', 'provider' => 'payoneer', 'currency' => 'USD'],
            ['name' => 'Pakistani Bank', 'slug' => 'pakistani-bank', 'provider' => 'pakistani_bank', 'currency' => 'PKR'],
            ['name' => 'Stripe', 'slug' => 'stripe', 'provider' => 'stripe', 'currency' => 'USD'],
        ];

        foreach ($accounts as $account) {
            PaymentAccount::updateOrCreate(['slug' => $account['slug']], $account + ['is_active' => true]);
        }
    }
}
