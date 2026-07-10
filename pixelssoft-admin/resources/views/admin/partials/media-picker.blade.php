@php
    $recentMedia = \App\Models\Media::latest()->take(8)->get();
@endphp
@if($recentMedia->count())
<div class="media-picker" data-target="{{ $target ?? 'image' }}">
    <p class="hint" style="width:100%;margin-bottom:4px;">Click a thumbnail to use its URL:</p>
    @foreach($recentMedia as $item)
        <img src="{{ \App\Support\MediaUrl::absolute($item->path) }}"
             alt="{{ $item->alt_text }}"
             title="{{ $item->filename }}"
             onclick="document.querySelector('[name={{ $target ?? 'image' }}]').value='{{ $item->path }}'">
    @endforeach
    <a href="{{ route('admin.media.index') }}" class="btn btn-sm btn-outline" style="align-self:center;">Upload more</a>
</div>
@else
<p class="hint"><a href="{{ route('admin.media.index') }}">Upload images</a> to the media library for quick selection.</p>
@endif
