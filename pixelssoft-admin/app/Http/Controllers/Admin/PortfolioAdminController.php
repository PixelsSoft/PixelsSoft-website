<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Portfolio;
use Illuminate\Http\Request;

class PortfolioAdminController extends Controller
{
    public function index(Request $request)
    {
        $portfolios = Portfolio::query()
            ->when($request->filled('q'), function ($query) use ($request) {
                $q = '%' . $request->string('q') . '%';
                $query->where(function ($inner) use ($q) {
                    $inner->where('title', 'like', $q)
                        ->orWhere('category', 'like', $q)
                        ->orWhere('client', 'like', $q);
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->orderBy('sort_order')
            ->paginate(15)
            ->withQueryString();

        return view('admin.portfolios.index', compact('portfolios'));
    }

    public function create()
    {
        return view('admin.portfolios.form', ['portfolio' => new Portfolio()]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['slug'] = DashboardController::makeSlug($data['title'], Portfolio::class);
        $data['tags'] = array_filter(array_map('trim', explode(',', $request->input('tags', ''))));
        Portfolio::create($data);
        return redirect()->route('admin.portfolios.index')->with('success', 'Portfolio item created.');
    }

    public function edit(Portfolio $portfolio)
    {
        return view('admin.portfolios.form', compact('portfolio'));
    }

    public function update(Request $request, Portfolio $portfolio)
    {
        $data = $this->validated($request);
        $data['tags'] = array_filter(array_map('trim', explode(',', $request->input('tags', ''))));
        $portfolio->update($data);
        return redirect()->route('admin.portfolios.index')->with('success', 'Portfolio item updated.');
    }

    public function destroy(Portfolio $portfolio)
    {
        $portfolio->delete();
        return redirect()->route('admin.portfolios.index')->with('success', 'Portfolio item deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|string',
            'client' => 'nullable|string|max:255',
            'project_date' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer',
            'status' => 'required|in:draft,published',
        ]);
    }
}
