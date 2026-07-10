@extends('admin.layout')

@section('title', 'Page Content')

@section('content')

<div class="card">

    <div class="card-header"><h2>Page Sections</h2></div>

    <p style="color:#6b7280;margin-bottom:20px;font-size:14px;">Edit JSON content for homepage and about page sections. These are served via <code>/api/v1/sections/{page}</code>.</p>

    <div class="table-wrap">

        <table>

            <thead><tr><th>Page</th><th>Section Key</th><th>Actions</th></tr></thead>

            <tbody>

            @forelse($sections as $section)

                <tr>

                    <td><span class="badge badge-published">{{ $section->page_key }}</span></td>

                    <td><strong>{{ $section->section_key }}</strong></td>

                    <td><a href="{{ route('admin.sections.edit', $section) }}" class="btn btn-sm btn-outline">Edit JSON</a></td>

                </tr>

            @empty

                <tr><td colspan="3"><div class="empty-state"><h3>No sections found</h3><p>Run <code>php artisan db:seed</code> to import section data.</p></div></td></tr>

            @endforelse

            </tbody>

        </table>

    </div>

</div>

@endsection

