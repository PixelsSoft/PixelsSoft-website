<?php

namespace App\Http\Controllers\Admin\Crm;

use App\Http\Controllers\Controller;
use App\Models\Crm\Company;
use App\Models\User;
use Illuminate\Http\Request;

class CompanyAdminController extends Controller
{
    public function index()
    {
        $companies = Company::with('owner')->withCount('contacts', 'deals')->latest()->paginate(20);

        return view('admin.crm.companies.index', compact('companies'));
    }

    public function create()
    {
        return view('admin.crm.companies.form', [
            'company' => new Company(),
            'users' => User::where('status', 'active')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        Company::create($this->validated($request));

        return redirect()->route('admin.crm.companies.index')->with('success', 'Company created.');
    }

    public function show(Company $company)
    {
        $company->load(['contacts', 'deals.stage', 'leads', 'owner']);

        return view('admin.crm.companies.show', compact('company'));
    }

    public function edit(Company $company)
    {
        return view('admin.crm.companies.form', [
            'company' => $company,
            'users' => User::where('status', 'active')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Company $company)
    {
        $company->update($this->validated($request));

        return redirect()->route('admin.crm.companies.index')->with('success', 'Company updated.');
    }

    public function destroy(Company $company)
    {
        $company->delete();

        return redirect()->route('admin.crm.companies.index')->with('success', 'Company deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'industry' => 'nullable|string|max:100',
            'website' => 'nullable|url|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'status' => 'required|in:active,inactive',
            'source' => 'nullable|string|max:100',
            'owner_id' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
        ]);
    }
}
