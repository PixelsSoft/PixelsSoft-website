@extends('admin.layout')

@section('title', 'Freelancer Portfolio')

@section('content')
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
<div class="page-header" style="display:flex;justify-content:space-between;gap:1rem;flex-wrap:wrap;margin-bottom:1rem;">
    <h1 style="margin:0;">Portfolio</h1>
    <form method="post" action="{{ route('admin.freelancer.portfolio.sync') }}">@csrf<button class="btn btn-outline btn-sm" type="submit">Sync Portfolio</button></form>
</div>
<div class="card">
    <div class="table-wrap">
        <table>
            <thead><tr><th>ID</th><th>Title</th><th>Category</th><th>Settings</th><th></th></tr></thead>
            <tbody>
                @forelse($portfolios as $item)
                    <tr>
                        <td>{{ $item->freelancer_portfolio_id ?: '—' }}</td>
                        <td>
                            <strong>{{ $item->title }}</strong>
                            <div class="text-muted">{{ Str::limit($item->description, 80) }}</div>
                        </td>
                        <td>{{ $item->category ?: '—' }}</td>
                        <td>
                            <form method="post" action="{{ route('admin.freelancer.portfolio.update', $item) }}" class="table-row-form">
                                @csrf @method('PUT')
                                <label class="form-check"><input type="checkbox" name="is_enabled" value="1" @checked($item->is_enabled)> Enabled</label>
                                <label class="form-check"><input type="checkbox" name="use_for_bidding" value="1" @checked($item->use_for_bidding)> Bidding</label>
                                <button class="btn btn-sm btn-outline" type="submit">Save</button>
                            </form>
                        </td>
                        <td>@if($item->url)<a href="{{ $item->url }}" target="_blank" rel="noopener">Open</a>@endif</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="empty-state">No portfolio items. Sync after connecting.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="padding:1rem;">{{ $portfolios->links() }}</div>
</div>
@endsection
