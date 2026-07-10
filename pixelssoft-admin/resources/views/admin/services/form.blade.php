@extends('admin.layout')

@section('title', $service->exists ? 'Edit Service' : 'New Service')

@section('content')

<div class="card">

    <div class="card-header"><h2>{{ $service->exists ? 'Edit Service' : 'Add Service' }}</h2></div>

    @include('admin.partials.errors')

    <form method="POST" action="{{ $service->exists ? route('admin.services.update', $service) : route('admin.services.store') }}">

        @csrf @if($service->exists) @method('PUT') @endif

        <div class="form-group"><label>Title *</label><input name="title" value="{{ old('title', $service->title) }}" required></div>

        <div class="form-group"><label>Description</label><textarea name="description" rows="4">{{ old('description', $service->description) }}</textarea></div>

        <div class="form-grid">

            <div class="form-group">

                <label>Icon Class</label>

                <input name="icon" value="{{ old('icon', $service->icon) }}" placeholder="pe-7s-paint-bucket">

                <p class="hint">Uses Pixeden icon classes (pe-7s-*).</p>

            </div>

            <div class="form-group"><label>Link</label><input name="link" value="{{ old('link', $service->link) }}"></div>

        </div>

        <div class="form-grid">

            <div class="form-group"><label>Sort Order</label><input type="number" name="sort_order" value="{{ old('sort_order', $service->sort_order ?? 0) }}"></div>

            <div class="form-group">

                <label>Status</label>

                <select name="status">

                    <option value="published" @selected(old('status', $service->status ?? 'published') === 'published')>Published</option>

                    <option value="draft" @selected(old('status', $service->status) === 'draft')>Draft</option>

                </select>

            </div>

        </div>

        <div class="form-actions">

            <button class="btn btn-primary">Save Service</button>

            <a href="{{ route('admin.services.index') }}" class="btn btn-secondary">Cancel</a>

        </div>

    </form>

</div>

@endsection

