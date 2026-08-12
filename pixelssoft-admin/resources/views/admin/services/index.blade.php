@extends('admin.layout')

@section('title', 'Services')

@section('content')
<div class="card">
    <div class="card-header">
        <h2>Services</h2>
        <a href="{{ route('admin.services.create') }}" class="btn btn-primary">+ Add Service</a>
    </div>

    @include('admin.partials.list-toolbar', [
        'showSearch' => true,
        'searchPlaceholder' => 'Search services…',
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
            <thead><tr><th>Title</th><th>Icon</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
            @forelse($services as $service)
                <tr>
                    <td><strong>{{ $service->title }}</strong></td>
                    <td><code>{{ $service->icon ?? '—' }}</code></td>
                    <td><span class="badge {{ $service->status === 'published' ? 'badge-published' : 'badge-draft' }}">{{ $service->status }}</span></td>
                    <td class="table-actions">
                        <a href="{{ route('admin.services.edit', $service) }}" class="btn btn-sm btn-outline">Edit</a>
                        <form action="{{ route('admin.services.destroy', $service) }}" method="POST" onsubmit="return confirm('Delete?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4"><div class="empty-state"><h3>No services</h3><p>Services appear on the homepage.</p></div></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $services->links() }}
</div>
@endsection
