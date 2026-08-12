@extends('admin.layout')

@section('title', 'Portfolio')

@section('content')
<div class="card">
    <div class="card-header">
        <h2>Portfolio Items</h2>
        <a href="{{ route('admin.portfolios.create') }}" class="btn btn-primary">+ Add Item</a>
    </div>

    @include('admin.partials.list-toolbar', [
        'showSearch' => true,
        'searchPlaceholder' => 'Search portfolio…',
        'filters' => [
            [
                'name' => 'status',
                'label' => 'All statuses',
                'options' => [
                    'published' => 'Published',
                    'draft' => 'Draft',
                ],
            ],
        ],
    ])

    <div class="table-wrap">
        <table>
            <thead><tr><th>Title</th><th>Category</th><th>Tags</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
            @forelse($portfolios as $item)
                <tr>
                    <td><strong>{{ $item->title }}</strong></td>
                    <td><span class="badge badge-published">{{ $item->category ?? '—' }}</span></td>
                    <td>{{ is_array($item->tags) ? implode(', ', $item->tags) : '—' }}</td>
                    <td><span class="badge {{ $item->status === 'published' ? 'badge-published' : 'badge-draft' }}">{{ $item->status }}</span></td>
                    <td class="table-actions">
                        <a href="{{ route('admin.portfolios.edit', $item) }}" class="btn btn-sm btn-outline">Edit</a>
                        <form action="{{ route('admin.portfolios.destroy', $item) }}" method="POST" onsubmit="return confirm('Delete?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5"><div class="empty-state"><h3>No portfolio items</h3><p>Add projects to display on the portfolio page.</p></div></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $portfolios->links() }}
</div>
@endsection
