@extends('admin.layout')

@section('title', 'Portfolio Links')

@section('content')
@php use Illuminate\Support\Str; @endphp
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-error">{{ session('error') }}</div>@endif

<div class="page-header" style="margin-bottom:1rem;">
    <h1 style="margin:0;">Portfolio Links</h1>
    <p class="text-muted" style="margin:.25rem 0 0;">CRM-managed work URLs with multiple skills and categories for automatic bid selection.</p>
</div>

<div class="card" style="margin-bottom:1rem;">
    <div class="card-header"><h2 style="margin:0;">Add Portfolio Work</h2></div>
    <div class="card-body">
        <form method="post" action="{{ route('admin.freelancer.portfolio-links.store') }}" class="admin-form admin-form-wide">
            @csrf
            <div class="form-row">
                <label>Title<input class="form-control" name="title" required></label>
                <label>URL<input class="form-control" name="url" type="url" required placeholder="https://..."></label>
                <label>Image URL<input class="form-control" name="image" type="url" placeholder="https://..."></label>
                <label>Priority<input class="form-control" type="number" name="priority" value="100"></label>
            </div>
            <label>Description<textarea class="form-control" name="description" rows="4"></textarea></label>
            <label>Technologies<textarea class="form-control textarea-sm" name="technologies" rows="2" placeholder="React Native, TypeScript, Firebase"></textarea></label>
            <div class="form-split">
                <label>Skills
                    <select class="form-control" name="skill_ids[]" multiple size="8">@foreach($skills as $skill)<option value="{{ $skill->id }}">{{ $skill->name }}</option>@endforeach</select>
                </label>
                <label>Categories
                    <select class="form-control" name="category_ids[]" multiple size="8">@foreach($categories as $category)<option value="{{ $category->id }}">{{ $category->name }}</option>@endforeach</select>
                </label>
            </div>
            <label class="form-check"><input type="checkbox" name="is_active" value="1" checked> Active</label>
            <button class="btn btn-primary" type="submit">Add Portfolio Work</button>
        </form>
    </div>
</div>

<form method="get" class="search-toolbar">
    <input class="form-control" type="search" name="q" value="{{ request('q') }}" placeholder="Search title or URL">
</form>

<div class="card">
    <div class="table-wrap">
        <table>
            <thead><tr><th>Title</th><th>URL</th><th>Skills</th><th>Categories</th><th>Priority</th><th>Active</th><th>Usage</th><th>Last Used</th><th>Actions</th></tr></thead>
            <tbody>
                @forelse($links as $link)
                    <tr>
                        <td>{{ $link->title }}<div class="text-muted">{{ Str::limit($link->description, 80) }}</div></td>
                        <td><a href="{{ $link->url }}" target="_blank" rel="noopener">{{ Str::limit($link->url, 40) }}</a></td>
                        <td>{{ $link->skills->pluck('name')->implode(', ') ?: '—' }}</td>
                        <td>{{ $link->categories->pluck('name')->implode(', ') ?: '—' }}</td>
                        <td>{{ $link->priority }}</td>
                        <td>{{ $link->is_active ? 'Yes' : 'No' }}</td>
                        <td>{{ $link->usage_count }}</td>
                        <td>{{ $link->last_used_at ?: '—' }}</td>
                        <td style="white-space:nowrap;">
                            <form method="post" action="{{ route('admin.freelancer.portfolio-links.test', $link) }}" style="display:inline;">@csrf<button class="btn btn-sm btn-outline" type="submit">Test URL</button></form>
                            <form method="post" action="{{ route('admin.freelancer.portfolio-links.destroy', $link) }}" style="display:inline;" onsubmit="return confirm('Delete portfolio link?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline" type="submit">Delete</button></form>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="9">
                            <form method="post" action="{{ route('admin.freelancer.portfolio-links.update', $link) }}" class="admin-form admin-form-wide inline-table-form">
                                @csrf @method('PUT')
                                <div class="form-row">
                                    <label>Title<input class="form-control" name="title" value="{{ $link->title }}"></label>
                                    <label>URL<input class="form-control" name="url" value="{{ $link->url }}"></label>
                                    <label>Priority<input class="form-control" type="number" name="priority" value="{{ $link->priority }}"></label>
                                </div>
                                <label>Description<textarea class="form-control textarea-sm" name="description" rows="2">{{ $link->description }}</textarea></label>
                                <label>Technologies<textarea class="form-control textarea-sm" name="technologies" rows="2">{{ implode(', ', $link->technologies ?? []) }}</textarea></label>
                                <div class="form-split">
                                    <label>Skills
                                        <select class="form-control select-sm" name="skill_ids[]" multiple size="5">@foreach($skills as $skill)<option value="{{ $skill->id }}" @selected($link->skills->contains('id', $skill->id))>{{ $skill->name }}</option>@endforeach</select>
                                    </label>
                                    <label>Categories
                                        <select class="form-control select-sm" name="category_ids[]" multiple size="5">@foreach($categories as $category)<option value="{{ $category->id }}" @selected($link->categories->contains('id', $category->id))>{{ $category->name }}</option>@endforeach</select>
                                    </label>
                                </div>
                                <div class="form-actions">
                                    <label class="form-check"><input type="checkbox" name="is_active" value="1" @checked($link->is_active)> Active</label>
                                    <button class="btn btn-sm btn-primary" type="submit">Save Changes</button>
                                </div>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="empty-state">No portfolio links yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="padding:1rem;">{{ $links->links() }}</div>
</div>
@endsection
