@extends('admin.layout')

@section('title', $portfolio->exists ? 'Edit Portfolio' : 'New Portfolio')

@section('content')

<div class="card">

    <div class="card-header">

        <h2>{{ $portfolio->exists ? 'Edit Portfolio Item' : 'Add Portfolio Item' }}</h2>

    </div>

    @include('admin.partials.errors')

    <form method="POST" action="{{ $portfolio->exists ? route('admin.portfolios.update', $portfolio) : route('admin.portfolios.store') }}">

        @csrf

        @if($portfolio->exists) @method('PUT') @endif



        <div class="form-grid">

            <div class="form-group">

                <label>Title *</label>

                <input name="title" value="{{ old('title', $portfolio->title) }}" required>

            </div>

            <div class="form-group">

                <label>Category (filter)</label>

                <select name="category">

                    <option value="">— Select —</option>

                    <option value="brand" @selected(old('category', $portfolio->category) === 'brand')>Branding</option>

                    <option value="web" @selected(old('category', $portfolio->category) === 'web')>Mobile App</option>

                    <option value="graphic" @selected(old('category', $portfolio->category) === 'graphic')>Creative</option>

                </select>

                <p class="hint">Controls the portfolio filter tabs on the website.</p>

            </div>

        </div>



        <div class="form-group">

            <label>Description</label>

            <textarea name="description" rows="4">{{ old('description', $portfolio->description) }}</textarea>

        </div>



        <div class="form-group">

            <label>Image URL</label>

            <input name="image" value="{{ old('image', $portfolio->image) }}" placeholder="/uploads/project.jpg">

            @include('admin.partials.media-picker', ['target' => 'image'])

        </div>



        <div class="form-grid">

            <div class="form-group">

                <label>Client</label>

                <input name="client" value="{{ old('client', $portfolio->client) }}">

            </div>

            <div class="form-group">

                <label>Project Date</label>

                <input name="project_date" value="{{ old('project_date', $portfolio->project_date) }}" placeholder="e.g. 2025">

            </div>

        </div>



        <div class="form-group">

            <label>Tags (comma separated)</label>

            <input name="tags" value="{{ old('tags', is_array($portfolio->tags) ? implode(', ', $portfolio->tags) : '') }}" placeholder="Design, WordPress, UI/UX">

        </div>



        <div class="form-grid">

            <div class="form-group">

                <label>Sort Order</label>

                <input type="number" name="sort_order" value="{{ old('sort_order', $portfolio->sort_order ?? 0) }}">

            </div>

            <div class="form-group">

                <label>Status</label>

                <select name="status">

                    <option value="published" @selected(old('status', $portfolio->status ?? 'published') === 'published')>Published</option>

                    <option value="draft" @selected(old('status', $portfolio->status) === 'draft')>Draft</option>

                </select>

            </div>

        </div>



        <div class="form-actions">

            <button class="btn btn-primary">Save Portfolio</button>

            <a href="{{ route('admin.portfolios.index') }}" class="btn btn-secondary">Cancel</a>

        </div>

    </form>

</div>

@endsection

