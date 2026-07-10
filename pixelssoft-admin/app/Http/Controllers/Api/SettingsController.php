<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GoogleIntegration;
use App\Models\SiteSetting;
use Illuminate\Http\JsonResponse;

class SettingsController extends Controller
{
    public function index(): JsonResponse
    {
        $settings = SiteSetting::pluck('value', 'key');

        return response()->json(['data' => $settings]);
    }

    public function google(): JsonResponse
    {
        $integrations = GoogleIntegration::all();
        $public = [];

        foreach ($integrations as $integration) {
            if (!$integration->enabled) {
                continue;
            }

            $config = $integration->config ?? [];

            switch ($integration->service_key) {
                case 'analytics':
                    $public['analytics'] = [
                        'enabled' => true,
                        'measurement_id' => $config['measurement_id'] ?? null,
                    ];
                    break;
                case 'gtm':
                    $public['gtm'] = [
                        'enabled' => true,
                        'container_id' => $config['container_id'] ?? null,
                    ];
                    break;
                case 'adsense':
                    $public['adsense'] = [
                        'enabled' => true,
                        'publisher_id' => $config['publisher_id'] ?? null,
                        'slots' => $config['slots'] ?? [],
                    ];
                    break;
                case 'search_console':
                    $public['search_console'] = [
                        'enabled' => true,
                        'verification_code' => $config['verification_code'] ?? null,
                    ];
                    break;
                case 'recaptcha':
                    $public['recaptcha'] = [
                        'enabled' => true,
                        'site_key' => $config['site_key'] ?? null,
                    ];
                    break;
                case 'maps':
                    $public['maps'] = [
                        'enabled' => true,
                        'embed_url' => $config['embed_url'] ?? null,
                        'lat' => $config['lat'] ?? null,
                        'lng' => $config['lng'] ?? null,
                        'zoom' => $config['zoom'] ?? 14,
                    ];
                    break;
                case 'google_ads':
                    $public['google_ads'] = [
                        'enabled' => true,
                        'conversion_id' => $config['conversion_id'] ?? null,
                        'conversion_label' => $config['conversion_label'] ?? null,
                    ];
                    break;
            }
        }

        return response()->json(['data' => empty($public) ? (object) [] : $public]);
    }
}
