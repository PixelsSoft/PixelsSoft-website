@extends('admin.layout')

@section('title', 'Showcase')

@section('content')

<div class="card">

    <div class="card-header">

        <h2>Showcase Slides</h2>

        <a href="{{ route('admin.showcases.create') }}" class="btn btn-primary">+ Add Slide</a>

    </div>

    <div class="table-wrap">

        <table>

            <thead><tr><th>Title</th><th>Link</th><th>Status</th><th>Actions</th></tr></thead>

            <tbody>

            @forelse($showcases as $item)

                <tr>

                    <td><strong>{{ $item->title_line1 }}</strong> {{ $item->title_line2 }}</td>

                    <td>{{ $item->link ?? '—' }}</td>

                    <td><span class="badge {{ $item->status === 'published' ? 'badge-published' : 'badge-draft' }}">{{ $item->status }}</span></td>

                    <td class="table-actions">

                        <a href="{{ route('admin.showcases.edit', $item) }}" class="btn btn-sm btn-outline">Edit</a>

                        <form action="{{ route('admin.showcases.destroy', $item) }}" method="POST" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button class="btn btn-danger">Delete</button></form>

                    </td>

                </tr>

            @empty

                <tr><td colspan="4"><div class="empty-state"><h3>No showcase slides</h3><p>Add slides for the homepage showcase section.</p></div></td></tr>

            @endforelse

            </tbody>

        </table>

    </div>

    {{ $showcases->links() }}

</div>

@endsection

