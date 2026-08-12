@extends('admin.layout')

@section('title', 'Media Library')

@section('content')

<div class="card">

    <div class="card-header"><h2>Upload Image</h2></div>

    @include('admin.partials.errors')

    <form method="POST" action="{{ route('admin.media.store') }}" enctype="multipart/form-data">

        @csrf

        <div class="form-grid">

            <div class="form-group">

                <label>Select Image</label>

                <input type="file" name="file" accept="image/*" required>

                <p class="hint">Max 5MB. JPG, PNG, GIF, WebP supported.</p>

            </div>

            <div class="form-group">

                <label>Alt Text</label>

                <input name="alt_text" placeholder="Describe the image for accessibility">

            </div>

        </div>

        <button class="btn btn-primary">Upload</button>

    </form>

</div>



<div class="card">

    <div class="card-header"><h2>Media Files ({{ $media->total() }})</h2></div>

    @include('admin.partials.list-toolbar', [
        'showSearch' => true,
        'searchPlaceholder' => 'Search media…',
    ])

    @if($media->count())

        <div class="media-grid">

            @foreach($media as $item)

                <div class="media-item">

                    <img src="{{ \App\Support\MediaUrl::absolute($item->path) }}" alt="{{ $item->alt_text }}">

                    <div class="media-item-info">

                        <div class="filename" title="{{ $item->filename }}">{{ $item->filename }}</div>

                        <input readonly value="{{ $item->path }}" style="width:100%;font-size:11px;margin:6px 0;padding:4px 6px;border:1px solid #e5e7eb;border-radius:4px;" onclick="this.select();document.execCommand('copy')">

                        <div style="display:flex;gap:6px;">

                            <button type="button" class="btn btn-sm btn-outline" onclick="navigator.clipboard.writeText('{{ $item->path }}')">Copy URL</button>

                            <form action="{{ route('admin.media.destroy', $item) }}" method="POST" onsubmit="return confirm('Delete this file?')">@csrf @method('DELETE')<button class="btn btn-danger">Delete</button></form>

                        </div>

                    </div>

                </div>

            @endforeach

        </div>

        {{ $media->links() }}

    @else

        <div class="empty-state"><h3>No media uploaded</h3><p>Upload images to use in blogs, portfolio, and showcase.</p></div>

    @endif

</div>

@endsection

