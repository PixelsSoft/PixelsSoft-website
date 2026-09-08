@extends('admin.layout')

@section('title', 'Lead Sources')

@section('content')
<div class="card">
    <div class="card-header">
        <h2>Lead sources</h2>
        <span class="badge badge-draft">{{ $sources->count() }} sources</span>
    </div>
    <div class="page-help">
        <p><strong>Set commissions once here.</strong> Upwork, Freelancer.com, Direct — each has its own platform fee and sales %.</p>
        <p>When a deal is won, the project copies these rates. Changing a % later does not rewrite old projects.</p>
    </div>

    <form method="POST" action="{{ route('admin.crm.sources.store') }}" class="source-add-bar">
        @csrf
        <div class="source-add-title">Add a source</div>
        <div class="source-add-fields">
            <input type="text" name="name" placeholder="Name (e.g. Upwork)" required maxlength="100">
            <select name="type" required>
                <option value="freelance_portal">Freelance portal</option>
                <option value="direct">Direct / website</option>
                <option value="referral">Referral</option>
                <option value="other">Other</option>
            </select>
            <input type="number" step="0.01" min="0" max="100" name="platform_commission_percent" value="10" required title="Platform fee %">
            <input type="number" step="0.01" min="0" max="100" name="default_sales_commission_percent" value="5" required title="Sales commission %">
            <button type="submit" class="btn btn-primary">Add</button>
        </div>
        <p class="source-add-hint">Platform % is what Freelancer.com / Upwork takes. Sales % is what the person who locks the deal earns.</p>
    </form>

    <div class="table-wrap">
        <table class="compact-table">
            <thead>
                <tr>
                    <th>Source</th>
                    <th>Type</th>
                    <th class="num">Platform fee %</th>
                    <th class="num">Sales commission %</th>
                    <th>Active</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($sources as $source)
                    <tr>
                        <td>
                            <form method="POST" action="{{ route('admin.crm.sources.update', $source) }}" id="source-form-{{ $source->id }}">
                                @csrf @method('PUT')
                                <input type="text" name="name" value="{{ $source->name }}" required>
                            </form>
                        </td>
                        <td>
                            <select name="type" form="source-form-{{ $source->id }}">
                                @foreach(['freelance_portal' => 'Freelance portal', 'direct' => 'Direct / website', 'referral' => 'Referral', 'other' => 'Other'] as $value => $label)
                                    <option value="{{ $value }}" @selected($source->type === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td class="num"><input type="number" step="0.01" min="0" max="100" name="platform_commission_percent" form="source-form-{{ $source->id }}" value="{{ $source->platform_commission_percent }}" required></td>
                        <td class="num"><input type="number" step="0.01" min="0" max="100" name="default_sales_commission_percent" form="source-form-{{ $source->id }}" value="{{ $source->default_sales_commission_percent }}" required></td>
                        <td>
                            <select name="is_active" form="source-form-{{ $source->id }}">
                                <option value="1" @selected($source->is_active)>Yes</option>
                                <option value="0" @selected(!$source->is_active)>No</option>
                            </select>
                        </td>
                        <td class="table-actions">
                            <button type="submit" form="source-form-{{ $source->id }}" class="btn btn-sm btn-primary">Save</button>
                            <form action="{{ route('admin.crm.sources.destroy', $source) }}" method="POST" onsubmit="return confirm('Remove {{ $source->name }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="empty-state">No sources yet. Add Upwork and Freelancer.com above.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
