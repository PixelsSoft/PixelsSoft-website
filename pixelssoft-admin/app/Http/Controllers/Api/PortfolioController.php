<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Portfolio;
use App\Support\MediaUrl;
use Illuminate\Http\JsonResponse;

class PortfolioController extends Controller
{
    public function index(): JsonResponse
    {
        $items = Portfolio::published()->get()->map(fn ($item) => [
            'id' => $item->id,
            'slug' => $item->slug,
            'title' => $item->title,
            'name' => $item->title,
            'category' => $item->category,
            'filterCategory' => $item->category,
            'description' => $item->description,
            'tags' => $item->tags ?? [],
            'image' => $item->image ? ['url' => MediaUrl::absolute($item->image)] : null,
        ]);

        return response()->json(['data' => $items]);
    }
}
