<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Models\GoogleIntegration;
use App\Models\PageSection;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class SettingsAdminController extends Controller
{
    public function index()
    {
        $settings = SiteSetting::pluck('value', 'key');
        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $allowed = [
            'contact_email', 'default_seo_title', 'default_seo_description',
            'social_facebook', 'social_instagram', 'social_linkedin',
            'tawk_property_id', 'tawk_widget_id',
        ];

        foreach ($allowed as $key) {
            if ($request->has($key)) {
                SiteSetting::setValue($key, $request->input($key));
            }
        }

        return back()->with('success', 'Settings saved.');
    }

    public function chat()
    {
        $settings = SiteSetting::pluck('value', 'key');
        return view('admin.settings.chat', compact('settings'));
    }

    public function google()
    {
        $integrations = GoogleIntegration::all()->keyBy('service_key');
        return view('admin.settings.google', compact('integrations'));
    }

    public function updateGoogle(Request $request)
    {
        $services = [
            'analytics' => ['measurement_id'],
            'gtm' => ['container_id'],
            'adsense' => ['publisher_id'],
            'search_console' => ['verification_code'],
            'recaptcha' => ['site_key', 'secret_key'],
            'maps' => ['embed_url', 'lat', 'lng', 'zoom'],
            'google_ads' => ['conversion_id', 'conversion_label'],
        ];

        foreach ($services as $serviceKey => $fields) {
            $config = [];
            foreach ($fields as $field) {
                if ($request->has("{$serviceKey}_{$field}")) {
                    $config[$field] = $request->input("{$serviceKey}_{$field}");
                }
            }

            if ($serviceKey === 'adsense') {
                $slots = [];
                $locations = $request->input('adsense_slot_locations', []);
                $ids = $request->input('adsense_slot_ids', []);
                foreach ($locations as $i => $location) {
                    if ($location && !empty($ids[$i])) {
                        $slots[$location] = $ids[$i];
                    }
                }
                $config['slots'] = $slots;

                if (!empty($config['publisher_id'])) {
                    $this->writeAdsTxt($config['publisher_id']);
                }
            }

            GoogleIntegration::updateOrCreate(
                ['service_key' => $serviceKey],
                [
                    'enabled' => $request->boolean("{$serviceKey}_enabled"),
                    'config' => $config,
                ]
            );
        }

        return back()->with('success', 'Google services updated.');
    }

    private function writeAdsTxt(string $publisherId): void
    {
        $pubId = str_replace('ca-pub-', '', $publisherId);
        $pubId = str_starts_with($publisherId, 'pub-') ? $publisherId : "pub-{$pubId}";
        if (str_starts_with($publisherId, 'ca-pub-')) {
            $pubId = str_replace('ca-pub-', 'pub-', $publisherId);
        }

        $content = "google.com, {$pubId}, DIRECT, f08c47fec0942fa0\n";
        File::put(public_path('ads.txt'), $content);
        File::put(storage_path('app/public/ads.txt'), $content);
    }

    public function sections(Request $request)
    {
        $sections = PageSection::query()
            ->when($request->filled('q'), function ($query) use ($request) {
                $q = '%' . $request->string('q') . '%';
                $query->where(function ($inner) use ($q) {
                    $inner->where('page_key', 'like', $q)
                        ->orWhere('section_key', 'like', $q);
                });
            })
            ->when($request->filled('page'), fn ($query) => $query->where('page_key', $request->string('page')))
            ->orderBy('page_key')
            ->orderBy('sort_order')
            ->paginate(30)
            ->withQueryString();

        return view('admin.sections.index', compact('sections'));
    }

    public function editSection(PageSection $section)
    {
        return view('admin.sections.form', compact('section'));
    }

    public function updateSection(Request $request, PageSection $section)
    {
        $content = json_decode($request->input('content_json'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return back()->with('error', 'Invalid JSON content.');
        }
        $section->update(['content' => $content]);
        return redirect()->route('admin.sections.index')->with('success', 'Section updated.');
    }

    public function messages(\Illuminate\Http\Request $request)
    {
        $messages = ContactMessage::query()
            ->when($request->filled('q'), function ($query) use ($request) {
                $q = '%' . $request->string('q') . '%';
                $query->where(function ($inner) use ($q) {
                    $inner->where('name', 'like', $q)
                        ->orWhere('email', 'like', $q)
                        ->orWhere('subject', 'like', $q)
                        ->orWhere('message', 'like', $q);
                });
            })
            ->when($request->input('status') === 'unread', fn ($query) => $query->where('is_read', false))
            ->when($request->input('status') === 'read', fn ($query) => $query->where('is_read', true))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.messages.index', compact('messages'));
    }

    public function markMessageRead(ContactMessage $message)
    {
        $message->update(['is_read' => true]);
        return back()->with('success', 'Message marked as read.');
    }

    public function destroyMessage(ContactMessage $message)
    {
        $message->delete();
        return back()->with('success', 'Message deleted.');
    }
}
