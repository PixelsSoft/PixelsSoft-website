<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Support\MediaUrl;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = min(max((int) $request->input('per_page', 9), 1), 24);
        $paginator = Blog::published()->latest('published_at')->paginate($perPage);

        return response()->json([
            'data' => $paginator->getCollection()->map(fn ($blog) => $this->transform($blog))->values(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    public function show(string $slug): JsonResponse
    {
        $blog = Blog::published()->where('slug', $slug)->firstOrFail();

        return response()->json(['data' => $this->transform($blog, true)]);
    }

    private function transform(Blog $blog, bool $full = false): array
    {
        $data = [
            'id' => $blog->id,
            '_id' => $blog->id,
            'slug' => $blog->slug,
            'title' => $blog->title,
            'excerpt' => $blog->excerpt,
            'author' => [
                'name' => $blog->author ?: 'Pixels Soft',
                'about' => null,
            ],
            'category' => $blog->category,
            'date' => $blog->published_at?->toIso8601String(),
            'image' => $blog->featured_image
                ? ['url' => MediaUrl::absolute($blog->featured_image)]
                : null,
        ];

        if ($full) {
            $data['content'] = $blog->content;
            $data['meta_title'] = $blog->meta_title;
            $data['meta_description'] = $blog->meta_description;
        }

        return $data;
    }
}
