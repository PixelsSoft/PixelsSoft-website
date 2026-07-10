@extends('admin.layout')

@section('title', 'Site Settings')

@section('content')

<div class="card">

    <div class="card-header"><h2>General Settings</h2></div>

    @include('admin.partials.errors')

    <form method="POST" action="{{ route('admin.settings.update') }}">

        @csrf

        <div class="form-grid">

            <div class="form-group">

                <label>Contact Email</label>

                <input name="contact_email" value="{{ $settings['contact_email'] ?? 'Info@pixelssoft.com' }}">

                <p class="hint">Displayed on the website contact page.</p>

            </div>

            <div class="form-group">

                <label>Default SEO Title</label>

                <input name="default_seo_title" value="{{ $settings['default_seo_title'] ?? 'Pixels Soft' }}">

            </div>

        </div>

        <div class="form-group">

            <label>Default SEO Description</label>

            <textarea name="default_seo_description" rows="3">{{ $settings['default_seo_description'] ?? '' }}</textarea>

        </div>

        <h3 style="margin:24px 0 16px;font-size:15px;">Social Links</h3>

        <div class="form-grid">

            <div class="form-group"><label>Facebook URL</label><input name="social_facebook" value="{{ $settings['social_facebook'] ?? '' }}"></div>

            <div class="form-group"><label>Instagram URL</label><input name="social_instagram" value="{{ $settings['social_instagram'] ?? '' }}"></div>

            <div class="form-group"><label>LinkedIn URL</label><input name="social_linkedin" value="{{ $settings['social_linkedin'] ?? '' }}"></div>

        </div>

        <div class="form-actions"><button class="btn btn-primary">Save Settings</button></div>

    </form>

</div>

@endsection

