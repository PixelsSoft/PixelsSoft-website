<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Http\Controllers\Controller;
use App\Models\Hr\Employee;
use App\Models\Hr\HrDocument;
use App\Support\MediaStorage;
use Illuminate\Http\Request;

class HrDocumentAdminController extends Controller
{
    public function index()
    {
        $documents = HrDocument::with('employee.user')->latest()->paginate(20);
        $employees = Employee::with('user')->where('status', 'active')->orderBy('employee_code')->get();

        return view('admin.hr.documents.index', compact('documents', 'employees'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'employee_id' => 'required|exists:hr_employees,id',
            'type' => 'required|in:contract,id,certificate,other',
            'title' => 'required|string|max:255',
            'expiry_date' => 'nullable|date',
            'file' => 'required|file|max:10240',
        ]);

        $data['file_path'] = MediaStorage::store($request->file('file'));
        unset($data['file']);
        HrDocument::create($data);

        return back()->with('success', 'Document uploaded.');
    }

    public function destroy(HrDocument $document)
    {
        MediaStorage::delete($document->file_path);
        $document->delete();

        return back()->with('success', 'Document deleted.');
    }
}
