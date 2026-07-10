@extends('admin.layout')



@section('title', $blog->exists ? 'Edit Blog' : 'New Blog')



@section('content')

<div class="card">

    <div class="card-header">

        <h2>{{ $blog->exists ? 'Edit Blog Post' : 'Create Blog Post' }}</h2>

    </div>

    @include('admin.partials.errors')

    <form method="POST" action="{{ $blog->exists ? route('admin.blogs.update', $blog) : route('admin.blogs.store') }}">

        @csrf

        @if($blog->exists) @method('PUT') @endif



        <div class="form-grid">

            <div class="form-group {{ $errors->has('title') ? 'has-error' : '' }}">

                <label>Title *</label>

                <input type="text" name="title" value="{{ old('title', $blog->title) }}" required>

                @error('title')<div class="field-error">{{ $message }}</div>@enderror

            </div>

            <div class="form-group">

                <label>Author</label>

                <input type="text" name="author" value="{{ old('author', $blog->author ?: 'Pixels Soft') }}">

            </div>

        </div>



        <div class="form-group">

            <label>Excerpt</label>

            <textarea name="excerpt" rows="3" placeholder="Short summary for blog listing...">{{ old('excerpt', $blog->excerpt) }}</textarea>

        </div>



        <div class="form-group">

            <label>Content (HTML)</label>

            <textarea name="content" rows="14" placeholder="Write your blog content in HTML...">{{ old('content', $blog->content) }}</textarea>

            <p class="hint">Supports HTML tags. Content appears on the website blog detail page.</p>

        </div>



        <div class="form-group">

            <label>Featured Image URL</label>

            <input type="text" name="featured_image" value="{{ old('featured_image', $blog->featured_image) }}" placeholder="/uploads/image.jpg">

            @include('admin.partials.media-picker', ['target' => 'featured_image'])

        </div>



        <div class="form-grid">

            <div class="form-group">

                <label>Category</label>

                <input type="text" name="category" value="{{ old('category', $blog->category) }}" placeholder="e.g. Technology">

            </div>

            <div class="form-group">

                <label>Status *</label>

                <select name="status">

                    <option value="draft" @selected(old('status', $blog->status) === 'draft')>Draft</option>

                    <option value="published" @selected(old('status', $blog->status) === 'published')>Published</option>

                </select>

            </div>

        </div>



        <div class="form-grid">

            <div class="form-group">

                <label>Published Date</label>

                <input type="datetime-local" name="published_at" value="{{ old('published_at', $blog->published_at?->format('Y-m-d\TH:i')) }}">

            </div>

            @if($blog->exists)

            <div class="form-group" style="display:flex;align-items:flex-end;">

                <label class="checkbox-label">

                    <input type="checkbox" name="regenerate_slug" value="1"> Regenerate URL slug from title

                </label>

            </div>

            @endif

        </div>



        <div class="form-grid">

            <div class="form-group">

                <label>Meta Title (SEO)</label>

                <input type="text" name="meta_title" value="{{ old('meta_title', $blog->meta_title) }}">

            </div>

            <div class="form-group">

                <label>Meta Description (SEO)</label>

                <textarea name="meta_description" rows="2">{{ old('meta_description', $blog->meta_description) }}</textarea>

            </div>

        </div>



        <div class="form-actions">

            <button type="submit" class="btn btn-primary">Save Blog Post</button>

            <a href="{{ route('admin.blogs.index') }}" class="btn btn-secondary">Cancel</a>

        </div>

    </form>

</div>

@endsection

