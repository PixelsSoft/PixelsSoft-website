<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PageSection;
use Illuminate\Http\JsonResponse;

class SectionController extends Controller
{
    public function show(string $page): JsonResponse
    {
        $sections = PageSection::where('page_key', $page)
            ->orderBy('sort_order')
            ->get()
            ->mapWithKeys(fn ($section) => [$section->section_key => $section->content]);

        return response()->json(['data' => $sections]);
    }
}
