<?php

namespace App\Http\Controllers\Admin\Crm;

use App\Http\Controllers\Controller;
use App\Models\Crm\Company;
use App\Models\Crm\Contact;
use Illuminate\Http\Request;

class ContactAdminController extends Controller
{
    public function index(Request $request)
    {
        $contacts = Contact::with('company')
            ->when($request->filled('q'), function ($query) use ($request) {
                $q = '%' . $request->string('q') . '%';
                $query->where(function ($inner) use ($q) {
                    $inner->where('name', 'like', $q)
                        ->orWhere('email', 'like', $q)
                        ->orWhere('phone', 'like', $q)
                        ->orWhereHas('company', fn ($c) => $c->where('name', 'like', $q));
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.crm.contacts.index', compact('contacts'));
    }

    public function create()
    {
        return view('admin.crm.contacts.form', [
            'contact' => new Contact(),
            'companies' => Company::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        Contact::create($this->validated($request));

        return redirect()->route('admin.crm.contacts.index')->with('success', 'Contact created.');
    }

    public function edit(Contact $contact)
    {
        return view('admin.crm.contacts.form', [
            'contact' => $contact,
            'companies' => Company::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Contact $contact)
    {
        $contact->update($this->validated($request));

        return redirect()->route('admin.crm.contacts.index')->with('success', 'Contact updated.');
    }

    public function destroy(Contact $contact)
    {
        $contact->delete();

        return redirect()->route('admin.crm.contacts.index')->with('success', 'Contact deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'company_id' => 'nullable|exists:crm_companies,id',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'job_title' => 'nullable|string|max:100',
            'is_primary' => 'nullable|boolean',
        ]);
    }
}
