<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Showcase;
use App\Support\MediaUrl;
use Illuminate\Http\JsonResponse;

class ShowcaseController extends Controller
{
    public function index(): JsonResponse
    {
        $items = Showcase::published()->get()->map(fn ($item) => [
            'id' => $item->id,
            'title' => [
                'first' => $item->title_line1,
                'second' => $item->title_line2,
            ],
            'image' => $item->image ? ['url' => MediaUrl::absolute($item->image)] : null,
            'link' => $item->link,
        ]);

        return response()->json(['data' => $items]);
    }
}
