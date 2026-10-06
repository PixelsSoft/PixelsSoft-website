<?php

namespace App\Http\Controllers\Admin\Freelancer;

use App\Http\Controllers\Admin\Freelancer\Concerns\EnsuresFreelancerOwnership;
use App\Http\Controllers\Controller;
use App\Models\Freelancer\FreelancerCategory;
use App\Models\Freelancer\FreelancerPortfolioLink;
use App\Services\Freelancer\FreelancerAccountResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class FreelancerPortfolioLinkController extends Controller
{
    use EnsuresFreelancerOwnership;
    public function index(Request $request, FreelancerAccountResolver $resolver)
    {
        $account = $resolver->firstOrCreateForUser()->load(['skills']);
        $links = $account->portfolioLinks()
            ->with(['skills', 'categories'])
            ->when($request->filled('q'), fn ($q) => $q->where('title', 'like', '%'.$request->string('q').'%')->orWhere('url', 'like', '%'.$request->string('q').'%'))
            ->orderBy('priority')
            ->paginate(20)
            ->withQueryString();

        return view('admin.freelancer.portfolio-links.index', [
            'account' => $account,
            'links' => $links,
            'skills' => $account->skills()->orderBy('name')->get(),
            'categories' => FreelancerCategory::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request, FreelancerAccountResolver $resolver)
    {
        $account = $resolver->firstOrCreateForUser();
        $data = $this->validated($request);
        $link = $account->portfolioLinks()->create([
            'title' => $data['title'],
            'url' => $data['url'],
            'description' => $data['description'] ?? null,
            'image' => $data['image'] ?? null,
            'technologies' => $this->parseList($data['technologies'] ?? null),
            'priority' => $data['priority'] ?? 100,
            'is_active' => $request->boolean('is_active', true),
        ]);
        $link->skills()->sync($data['skill_ids'] ?? []);
        $link->categories()->sync($data['category_ids'] ?? []);

        return back()->with('success', 'Portfolio link created.');
    }

    public function update(Request $request, FreelancerPortfolioLink $portfolioLink)
    {
        $this->ensurePortfolioLinkOwned($portfolioLink);
        $data = $this->validated($request);
        $portfolioLink->fill([
            'title' => $data['title'],
            'url' => $data['url'],
            'description' => $data['description'] ?? null,
            'image' => $data['image'] ?? null,
            'technologies' => $this->parseList($data['technologies'] ?? null),
            'priority' => $data['priority'] ?? 100,
            'is_active' => $request->boolean('is_active'),
        ])->save();
        $portfolioLink->skills()->sync($data['skill_ids'] ?? []);
        $portfolioLink->categories()->sync($data['category_ids'] ?? []);

        return back()->with('success', 'Portfolio link updated.');
    }

    public function destroy(FreelancerPortfolioLink $portfolioLink)
    {
        $this->ensurePortfolioLinkOwned($portfolioLink);
        $portfolioLink->delete();

        return back()->with('success', 'Portfolio link deleted.');
    }

    public function test(FreelancerPortfolioLink $portfolioLink)
    {
        $this->ensurePortfolioLinkOwned($portfolioLink);

        try {
            $response = Http::timeout(10)->head($portfolioLink->url);
            if ($response->successful() || $response->status() === 405) {
                return back()->with('success', 'Portfolio URL responded successfully.');
            }

            return back()->with('error', 'Portfolio URL test failed with HTTP '.$response->status().'.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Portfolio URL test failed: '.$e->getMessage());
        }
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'url' => ['required', 'url', 'max:500'],
            'description' => ['nullable', 'string', 'max:5000'],
            'image' => ['nullable', 'url', 'max:500'],
            'technologies' => ['nullable', 'string'],
            'priority' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
            'skill_ids' => ['nullable', 'array'],
            'skill_ids.*' => ['integer', 'exists:freelancer_skills,id'],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['integer', 'exists:freelancer_categories,id'],
        ]);
    }

    protected function parseList(?string $value): array
    {
        return collect(preg_split('/[\n,]+/', (string) $value))->map(fn ($v) => trim($v))->filter()->values()->all();
    }
}
