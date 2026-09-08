<?php

namespace App\Http\Controllers\Admin\Crm;

use App\Http\Controllers\Controller;
use App\Models\Crm\AcquisitionSource;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AcquisitionSourceAdminController extends Controller
{
    public function index()
    {
        $sources = AcquisitionSource::orderBy('name')->get();

        return view('admin.crm.sources.index', compact('sources'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['slug'] = AcquisitionSource::makeSlug($data['name']);
        $data['is_active'] = $request->boolean('is_active', true);
        AcquisitionSource::create($data);

        return back()->with('success', 'Lead source added. New projects from this source will use these commission rates.');
    }

    public function update(Request $request, AcquisitionSource $source)
    {
        $data = $this->validated($request, $source);
        $data['is_active'] = $request->boolean('is_active');
        $source->update($data);

        return back()->with('success', 'Lead source updated. Existing projects keep their original rates.');
    }

    public function destroy(AcquisitionSource $source)
    {
        $source->delete();

        return back()->with('success', 'Source removed.');
    }

    private function validated(Request $request, ?AcquisitionSource $source = null): array
    {
        return $request->validate([
            'name' => [
                'required', 'string', 'max:100',
                Rule::unique('crm_acquisition_sources', 'name')->ignore($source?->id),
            ],
            'type' => 'required|in:freelance_portal,direct,referral,other',
            'platform_commission_percent' => 'required|numeric|min:0|max:100',
            'default_sales_commission_percent' => 'required|numeric|min:0|max:100',
            'notes' => 'nullable|string',
        ]);
    }
}
