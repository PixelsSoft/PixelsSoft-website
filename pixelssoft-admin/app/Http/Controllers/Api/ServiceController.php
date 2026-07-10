<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\JsonResponse;

class ServiceController extends Controller
{
    public function index(): JsonResponse
    {
        $services = Service::published()->get()->map(fn ($service) => [
            'id' => $service->id,
            'title' => $service->title,
            'description' => $service->description,
            'icon' => $service->icon,
            'link' => $service->link,
        ]);

        return response()->json(['data' => $services]);
    }
}
