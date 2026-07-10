@extends('admin.layout')

@section('title', $showcase->exists ? 'Edit Showcase' : 'New Showcase')

@section('content')

<div class="card">

    <div class="card-header"><h2>{{ $showcase->exists ? 'Edit Showcase Slide' : 'Add Showcase Slide' }}</h2></div>

    @include('admin.partials.errors')

    <form method="POST" action="{{ $showcase->exists ? route('admin.showcases.update', $showcase) : route('admin.showcases.store') }}">

        @csrf @if($showcase->exists) @method('PUT') @endif

        <div class="form-grid">

            <div class="form-group"><label>Title Line 1 *</label><input name="title_line1" value="{{ old('title_line1', $showcase->title_line1) }}" required></div>

            <div class="form-group"><label>Title Line 2</label><input name="title_line2" value="{{ old('title_line2', $showcase->title_line2) }}"></div>

        </div>

        <div class="form-group">

            <label>Image URL</label>

            <input name="image" value="{{ old('image', $showcase->image) }}">

            @include('admin.partials.media-picker', ['target' => 'image'])

        </div>

        <div class="form-grid">

            <div class="form-group"><label>Link</label><input name="link" value="{{ old('link', $showcase->link) }}" placeholder="/portfolio/"></div>

            <div class="form-group"><label>Sort Order</label><input type="number" name="sort_order" value="{{ old('sort_order', $showcase->sort_order ?? 0) }}"></div>

        </div>

        <div class="form-group">

            <label>Status</label>

            <select name="status">

                <option value="published" @selected(old('status', $showcase->status ?? 'published') === 'published')>Published</option>

                <option value="draft" @selected(old('status', $showcase->status) === 'draft')>Draft</option>

            </select>

        </div>

        <div class="form-actions">

            <button class="btn btn-primary">Save Slide</button>

            <a href="{{ route('admin.showcases.index') }}" class="btn btn-secondary">Cancel</a>

        </div>

    </form>

</div>

@endsection

