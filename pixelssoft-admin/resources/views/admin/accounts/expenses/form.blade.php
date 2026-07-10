@extends('admin.layout')

@section('title', 'Submit Expense')

@section('content')
<div class="card">
    <div class="card-header"><h2>Submit Expense</h2></div>
    <form method="POST" action="{{ route('admin.accounts.expenses.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="form-grid">
            <div class="form-group">
                <label>Category</label>
                <select name="category_id"><option value="">— None —</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" @selected(old('category_id', $expense->category_id) == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Project</label>
                <select name="project_id"><option value="">— None —</option>
                    @foreach($projects as $project)
                        <option value="{{ $project->id }}" @selected(old('project_id', $expense->project_id) == $project->id)>{{ $project->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group"><label>Amount *</label><input type="number" step="0.01" min="0.01" name="amount" value="{{ old('amount', $expense->amount) }}" required></div>
            <div class="form-group"><label>Date *</label><input type="date" name="date" value="{{ old('date', $expense->date?->format('Y-m-d') ?? now()->format('Y-m-d')) }}" required></div>
            <div class="form-group"><label>Vendor</label><input type="text" name="vendor" value="{{ old('vendor', $expense->vendor) }}"></div>
            <div class="form-group"><label>Receipt</label><input type="file" name="receipt" accept="image/*,.pdf"></div>
        </div>
        <div class="form-group" style="margin-top:16px"><label>Description</label><textarea name="description" rows="3">{{ old('description', $expense->description) }}</textarea></div>
        <div class="form-actions">
            <a href="{{ route('admin.accounts.expenses.index') }}" class="btn btn-outline">Cancel</a>
            <button type="submit" class="btn btn-primary">Submit Expense</button>
        </div>
    </form>
</div>
@endsection
