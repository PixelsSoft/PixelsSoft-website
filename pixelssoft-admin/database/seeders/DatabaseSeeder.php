<?php

namespace Database\Seeders;

use App\Models\GoogleIntegration;
use App\Models\PageSection;
use App\Models\Service;
use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RolesAndPermissionsSeeder::class);
        $this->call(CrmSeeder::class);
        $this->call(AccountsSeeder::class);
        $this->call(AgencyFinanceSeeder::class);
        $this->call(HrSeeder::class);
        $this->call(MigrateContactMessagesToLeadsSeeder::class);

        SiteSetting::setValue('contact_email', 'Info@pixelssoft.com');
        SiteSetting::setValue('tawk_property_id', '648864e494cf5d49dc5d6a94');
        SiteSetting::setValue('tawk_widget_id', '1h2qck7tf');
        SiteSetting::setValue('default_seo_title', 'Pixels Soft');
        SiteSetting::setValue('default_seo_description', 'Pixels Soft delivers creative web design, mobile apps, and digital marketing solutions.');

        $sectionsPath = base_path('../src/data/sections');
        if (is_dir($sectionsPath)) {
            $homeSections = ['intro', 'about-us1', 'numbers1', 'works1Slider', 'testimonials', 'skills-circle', 'clients1'];
            foreach ($homeSections as $key) {
                $file = "{$sectionsPath}/{$key}.json";
                if (file_exists($file)) {
                    PageSection::updateOrCreate(
                        ['page_key' => 'home', 'section_key' => $key],
                        ['content' => json_decode(file_get_contents($file), true), 'sort_order' => 0]
                    );
                }
            }

            $aboutSections = ['services4', 'clients1'];
            foreach ($aboutSections as $key) {
                $file = "{$sectionsPath}/{$key}.json";
                if (file_exists($file)) {
                    PageSection::updateOrCreate(
                        ['page_key' => 'about', 'section_key' => $key],
                        ['content' => json_decode(file_get_contents($file), true), 'sort_order' => 0]
                    );
                }
            }
        }

        $services = [
            ['title' => 'Graphic Design, Web & Mobile Design', 'description' => 'We create visuals that help you stand out, grab attention, and shine in a competitive market.', 'icon' => 'pe-7s-paint-bucket', 'sort_order' => 1],
            ['title' => 'Web & Mobile Development', 'description' => 'We develop excellent web and mobile app solutions that transform your digital operations.', 'icon' => 'pe-7s-phone', 'sort_order' => 2],
            ['title' => 'Social Media Marketing', 'description' => 'Our SEO and marketing team helps your brand rank faster and reach the right audience.', 'icon' => 'pe-7s-display1', 'sort_order' => 3],
        ];

        foreach ($services as $service) {
            Service::updateOrCreate(['title' => $service['title']], array_merge($service, ['status' => 'published']));
        }

        $googleServices = ['analytics', 'gtm', 'adsense', 'search_console', 'recaptcha', 'maps', 'google_ads'];
        foreach ($googleServices as $key) {
            GoogleIntegration::updateOrCreate(
                ['service_key' => $key],
                ['enabled' => false, 'config' => []]
            );
        }
    }
}
