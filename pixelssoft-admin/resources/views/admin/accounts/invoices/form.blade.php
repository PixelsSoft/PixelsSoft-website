@extends('admin.layout')

@section('title', $invoice->exists ? 'Edit Invoice' : 'New Invoice')

@section('content')
<div class="card">
    <div class="card-header"><h2>{{ $invoice->exists ? 'Edit Invoice' : 'Create Invoice' }}</h2></div>
    <form method="POST" action="{{ $invoice->exists ? route('admin.accounts.invoices.update', $invoice) : route('admin.accounts.invoices.store') }}">
        @csrf
        @if($invoice->exists) @method('PUT') @endif
        <div class="form-grid">
            @if($invoice->exists)
                <div class="form-group"><label>Invoice Number</label><input type="text" value="{{ $invoice->number }}" disabled></div>
            @endif
            <div class="form-group">
                <label>Company</label>
                <select name="company_id"><option value="">— None —</option>
                    @foreach($companies as $company)
                        <option value="{{ $company->id }}" @selected(old('company_id', $invoice->company_id) == $company->id)>{{ $company->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Project</label>
                <select name="project_id"><option value="">— None —</option>
                    @foreach($projects as $project)
                        <option value="{{ $project->id }}" @selected(old('project_id', $invoice->project_id) == $project->id)>{{ $project->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Deal</label>
                <select name="deal_id"><option value="">— None —</option>
                    @foreach($deals as $deal)
                        <option value="{{ $deal->id }}" @selected(old('deal_id', $invoice->deal_id) == $deal->id)>{{ $deal->title }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Status *</label>
                <select name="status" required>
                    @foreach(['draft','sent','partial','paid','overdue','void'] as $status)
                        <option value="{{ $status }}" @selected(old('status', $invoice->status ?? 'draft') === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group"><label>Issue Date *</label><input type="date" name="issue_date" value="{{ old('issue_date', $invoice->issue_date?->format('Y-m-d')) }}" required></div>
            <div class="form-group"><label>Due Date</label><input type="date" name="due_date" value="{{ old('due_date', $invoice->due_date?->format('Y-m-d')) }}"></div>
            <div class="form-group"><label>Tax</label><input type="number" step="0.01" min="0" name="tax" value="{{ old('tax', $invoice->tax ?? 0) }}"></div>
            <div class="form-group"><label>Currency</label><input type="text" name="currency" maxlength="3" value="{{ old('currency', $invoice->currency ?? 'USD') }}"></div>
        </div>
        <div class="form-group" style="margin-top:16px"><label>Notes</label><textarea name="notes" rows="3">{{ old('notes', $invoice->notes) }}</textarea></div>
        <div class="form-actions">
            <a href="{{ route('admin.accounts.invoices.index') }}" class="btn btn-outline">Cancel</a>
            <button type="submit" class="btn btn-primary">Save Invoice</button>
        </div>
    </form>
</div>
@endsection
