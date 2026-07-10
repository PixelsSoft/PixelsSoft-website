<?php

namespace App\Services;

use App\Models\Blog;
use Illuminate\Support\Str;
use PDO;

class WordPressBlogImporter
{
    public function __construct(
        private readonly string $importDatabase,
    ) {}

    public function import(bool $skipExisting = true): array
    {
        $pdo = $this->importPdo();

        $posts = $pdo->query("
            SELECT ID, post_author, post_date, post_content, post_title, post_excerpt,
                   post_status, post_name, post_modified
            FROM wp_posts
            WHERE post_type = 'post'
              AND post_status IN ('publish', 'draft')
              AND post_title <> ''
            ORDER BY post_date ASC
        ")->fetchAll(PDO::FETCH_ASSOC);

        $imported = 0;
        $skipped = 0;
        $updated = 0;

        foreach ($posts as $post) {
            $slug = Str::slug($post['post_name'] ?: $post['post_title']) ?: 'post-' . $post['ID'];

            if ($skipExisting && Blog::where('slug', $slug)->exists()) {
                $skipped++;
                continue;
            }

            $content = $this->normalizeContent($post['post_content']);

            $excerpt = trim($post['post_excerpt'] ?? '');
            if ($excerpt === '') {
                $excerpt = Str::limit(strip_tags($content), 220);
            }

            $payload = [
                'title' => html_entity_decode($post['post_title'], ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                'excerpt' => html_entity_decode($excerpt, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                'content' => $content,
                'featured_image' => $this->extractFeaturedImage($content),
                'author' => 'Pixels Soft',
                'category' => null,
                'meta_title' => html_entity_decode($post['post_title'], ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                'meta_description' => html_entity_decode($excerpt, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                'status' => $post['post_status'] === 'publish' ? 'published' : 'draft',
                'published_at' => $post['post_status'] === 'publish' ? $post['post_date'] : null,
            ];

            $existing = Blog::where('slug', $slug)->first();
            if ($existing) {
                $existing->update($payload);
                $updated++;
            } else {
                Blog::create(array_merge($payload, ['slug' => $slug]));
                $imported++;
            }
        }

        return [
            'total_wp_posts' => count($posts),
            'imported' => $imported,
            'updated' => $updated,
            'skipped' => $skipped,
        ];
    }

    private function normalizeContent(string $content): string
    {
        $content = html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $content = preg_replace('/<!--\s*\/?wp:[^>]*-->/', '', $content) ?? $content;

        return trim($content);
    }

    private function extractFeaturedImage(string $content): ?string
    {
        if (preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $content, $matches)) {
            return $matches[1];
        }

        return null;
    }

    private function importPdo(): PDO
    {
        $config = config('database.connections.mysql');

        return new PDO(
            sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                $config['host'],
                $config['port'] ?? 3306,
                $this->importDatabase
            ),
            $config['username'],
            $config['password'],
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
    }
}
