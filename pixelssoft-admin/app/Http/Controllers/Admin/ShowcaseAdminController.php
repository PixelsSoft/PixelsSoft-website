<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Showcase;
use Illuminate\Http\Request;

class ShowcaseAdminController extends Controller
{
    public function index()
    {
        $showcases = Showcase::orderBy('sort_order')->paginate(15);
        return view('admin.showcases.index', compact('showcases'));
    }

    public function create()
    {
        return view('admin.showcases.form', ['showcase' => new Showcase()]);
    }

    public function store(Request $request)
    {
        Showcase::create($this->validated($request));
        return redirect()->route('admin.showcases.index')->with('success', 'Showcase slide created.');
    }

    public function edit(Showcase $showcase)
    {
        return view('admin.showcases.form', compact('showcase'));
    }

    public function update(Request $request, Showcase $showcase)
    {
        $showcase->update($this->validated($request));
        return redirect()->route('admin.showcases.index')->with('success', 'Showcase slide updated.');
    }

    public function destroy(Showcase $showcase)
    {
        $showcase->delete();
        return redirect()->route('admin.showcases.index')->with('success', 'Showcase slide deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title_line1' => 'required|string|max:255',
            'title_line2' => 'nullable|string|max:255',
            'image' => 'nullable|string',
            'link' => 'nullable|string',
            'sort_order' => 'nullable|integer',
            'status' => 'required|in:draft,published',
        ]);
    }
}
