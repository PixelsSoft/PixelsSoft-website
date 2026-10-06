@extends('admin.layout')

@section('title', 'Freelancer Skills')

@section('content')
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
<div class="page-header" style="display:flex;justify-content:space-between;gap:1rem;flex-wrap:wrap;margin-bottom:1rem;">
    <h1 style="margin:0;">Skills</h1>
    <form method="post" action="{{ route('admin.freelancer.skills.sync') }}">@csrf<button class="btn btn-outline btn-sm" type="submit">Sync Skills</button></form>
</div>

<form method="get" class="search-toolbar">
    <input type="search" name="q" value="{{ request('q') }}" placeholder="Search skills" class="form-control">
</form>

<div class="card">
    <div class="table-wrap">
        <table>
            <thead><tr><th>ID</th><th>Name</th><th>Settings</th><th></th></tr></thead>
            <tbody>
                @forelse($skills as $skill)
                    <tr>
                        <td>{{ $skill->freelancer_skill_id }}</td>
                        <td>{{ $skill->name }}</td>
                        <td>
                            <form method="post" action="{{ route('admin.freelancer.skills.update', $skill) }}" class="table-row-form">
                                @csrf @method('PUT')
                                <label class="form-check"><input type="checkbox" name="is_primary" value="1" @checked($skill->is_primary)> Primary</label>
                                <label class="form-check"><input type="checkbox" name="is_secondary" value="1" @checked($skill->is_secondary)> Secondary</label>
                                <label class="form-check"><input type="checkbox" name="automation_enabled" value="1" @checked($skill->automation_enabled)> Auto</label>
                                <label>Priority<input type="number" name="priority" value="{{ $skill->priority }}" class="form-control input-sm"></label>
                                <button class="btn btn-sm btn-outline" type="submit">Save</button>
                            </form>
                        </td>
                        <td>
                            <form method="post" action="{{ route('admin.freelancer.skills.destroy', $skill) }}">@csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline" type="submit">Remove</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="empty-state">No skills yet. Sync profile after connecting.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="padding:1rem;">{{ $skills->links() }}</div>
</div>
@endsection
