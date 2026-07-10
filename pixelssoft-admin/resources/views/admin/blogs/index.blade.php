@extends('admin.layout')



@section('title', 'Blogs')



@section('content')

<div class="card">

    <div class="card-header">

        <h2>All Blog Posts</h2>

        <a href="{{ route('admin.blogs.create') }}" class="btn btn-primary">+ New Blog</a>

    </div>

    <div class="table-wrap">

        <table>

            <thead>

                <tr><th>Title</th><th>Status</th><th>Category</th><th>Published</th><th>Actions</th></tr>

            </thead>

            <tbody>

                @forelse($blogs as $blog)

                    <tr>

                        <td><strong>{{ $blog->title }}</strong><br><small style="color:#6b7280">/blog/{{ $blog->slug }}</small></td>

                        <td>

                            <span class="badge {{ $blog->status === 'published' ? 'badge-published' : 'badge-draft' }}">

                                {{ $blog->status }}

                            </span>

                        </td>

                        <td>{{ $blog->category ?? '—' }}</td>

                        <td>{{ $blog->published_at?->format('M d, Y') ?? '—' }}</td>

                        <td class="table-actions">

                            <a href="{{ route('admin.blogs.edit', $blog) }}" class="btn btn-sm btn-outline">Edit</a>

                            <form action="{{ route('admin.blogs.destroy', $blog) }}" method="POST" onsubmit="return confirm('Delete this blog post?')">

                                @csrf @method('DELETE')

                                <button type="submit" class="btn btn-danger">Delete</button>

                            </form>

                        </td>

                    </tr>

                @empty

                    <tr><td colspan="5">

                        <div class="empty-state">

                            <h3>No blog posts yet</h3>

                            <p>Create your first blog post to display on the website.</p>

                            <a href="{{ route('admin.blogs.create') }}" class="btn btn-primary" style="margin-top:12px;">Create Blog Post</a>

                        </div>

                    </td></tr>

                @endforelse

            </tbody>

        </table>

    </div>

    {{ $blogs->links() }}

</div>

@endsection

