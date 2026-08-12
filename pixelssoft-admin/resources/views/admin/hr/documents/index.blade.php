@extends('admin.layout')

@section('title', 'HR Documents')

@section('content')
@can('hr.documents.manage')
<div class="card">
    <div class="card-header"><h2>Upload Document</h2></div>
    <form method="POST" action="{{ route('admin.hr.documents.store') }}" enctype="multipart/form-data" style="padding:0 24px 24px">
        @csrf
        @include('admin.partials.errors')
        <div class="form-grid">
            <div class="form-group">
                <label>Employee *</label>
                <select name="employee_id" required>
                    <option value="">Select employee…</option>
                    @foreach($employees as $employee)
                        <option value="{{ $employee->id }}" @selected(old('employee_id') == $employee->id)>
                            {{ $employee->employee_code }} — {{ $employee->user?->name ?? '—' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Type *</label>
                <select name="type" required>
                    @foreach(['contract' => 'Contract', 'id' => 'ID Document', 'certificate' => 'Certificate', 'other' => 'Other'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('type') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Title *</label>
                <input type="text" name="title" value="{{ old('title') }}" required maxlength="255">
            </div>
            <div class="form-group">
                <label>Expiry Date</label>
                <input type="date" name="expiry_date" value="{{ old('expiry_date') }}">
            </div>
            <div class="form-group">
                <label>File *</label>
                <input type="file" name="file" required accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
            </div>
        </div>
        <div class="form-actions" style="margin-top:12px;padding-top:0;border-top:none">
            <button type="submit" class="btn btn-primary">Upload Document</button>
        </div>
    </form>
</div>
@endcan

<div class="card">
    <div class="card-header"><h2>Documents</h2></div>

    @include('admin.partials.list-toolbar', [
        'showSearch' => true,
        'searchPlaceholder' => 'Search documents…',
        'filters' => [
            [
                'name' => 'type',
                'label' => 'All types',
                'options' => [
                    'contract' => 'Contract',
                    'id' => 'ID Document',
                    'certificate' => 'Certificate',
                    'other' => 'Other',
                ],
            ],
        ],
    ])

    <div class="table-wrap">
        <table>
            <thead><tr><th>Employee</th><th>Type</th><th>Title</th><th>Expiry</th><th>Uploaded</th><th>Actions</th></tr></thead>
            <tbody>
                @forelse($documents as $document)
                    <tr>
                        <td>{{ $document->employee?->user?->name ?? $document->employee?->employee_code ?? '—' }}</td>
                        <td><span class="badge badge-draft">{{ $document->type }}</span></td>
                        <td>{{ $document->title }}</td>
                        <td>
                            @if($document->expiry_date)
                                @if($document->expiry_date->isPast())
                                    <span class="badge score-hot">{{ $document->expiry_date->format('M d, Y') }}</span>
                                @else
                                    {{ $document->expiry_date->format('M d, Y') }}
                                @endif
                            @else
                                —
                            @endif
                        </td>
                        <td>{{ $document->created_at->format('M d, Y') }}</td>
                        <td class="actions">
                            <a href="{{ asset(ltrim($document->file_path, '/')) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline">Download</a>
                            @can('hr.documents.manage')
                                <form action="{{ route('admin.hr.documents.destroy', $document) }}" method="POST" onsubmit="return confirm('Delete this document?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="empty-state">No documents uploaded yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $documents->links() }}
</div>
@endsection
