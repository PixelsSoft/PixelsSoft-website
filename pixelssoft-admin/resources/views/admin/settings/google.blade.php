@extends('admin.layout')

@section('title', 'Google Services')

@section('content')

<div class="card">

    <p style="color:#6b7280;margin-bottom:20px;font-size:14px;">Manage Google integrations for your website. Settings sync to the frontend via API after cookie consent. Keep AdSense disabled until Google approves your site.</p>

    @include('admin.partials.errors')

    <form method="POST" action="{{ route('admin.settings.google.update') }}">

        @csrf

        @php $i = fn($key) => $integrations[$key] ?? null; @endphp



        <div class="service-block">

            <h3>Google Analytics (GA4)</h3>

            <label class="checkbox-label" style="margin-bottom:14px;"><input type="checkbox" name="analytics_enabled" value="1" @checked($i('analytics')?->enabled)> Enable Analytics</label>

            <div class="form-group"><label>Measurement ID</label><input name="analytics_measurement_id" value="{{ $i('analytics')?->config['measurement_id'] ?? '' }}" placeholder="G-XXXXXXXXXX"></div>

        </div>



        <div class="service-block">

            <h3>Google Tag Manager</h3>

            <label class="checkbox-label" style="margin-bottom:14px;"><input type="checkbox" name="gtm_enabled" value="1" @checked($i('gtm')?->enabled)> Enable GTM</label>

            <div class="form-group"><label>Container ID</label><input name="gtm_container_id" value="{{ $i('gtm')?->config['container_id'] ?? '' }}" placeholder="GTM-XXXXXXX"></div>

        </div>



        <div class="service-block">

            <h3>Google AdSense</h3>

            <label class="checkbox-label" style="margin-bottom:14px;"><input type="checkbox" name="adsense_enabled" value="1" @checked($i('adsense')?->enabled)> Enable AdSense (only after approval)</label>

            <div class="form-group"><label>Publisher ID</label><input name="adsense_publisher_id" value="{{ $i('adsense')?->config['publisher_id'] ?? '' }}" placeholder="ca-pub-XXXXXXXX"></div>

            <p class="hint">ads.txt is auto-generated when publisher ID is saved.</p>

            <h4 style="margin:16px 0 10px;font-size:13px;">Ad Slots</h4>

            @php $slots = $i('adsense')?->config['slots'] ?? []; @endphp

            @foreach(['home_below_hero' => 'Home — Below Hero', 'blog_sidebar' => 'Blog — Sidebar', 'portfolio_footer' => 'Portfolio — Footer'] as $loc => $label)

                <div class="form-grid" style="margin-bottom:8px;">

                    <input type="hidden" name="adsense_slot_locations[]" value="{{ $loc }}">

                    <div class="form-group" style="margin:0;"><label>{{ $label }}</label></div>

                    <div class="form-group" style="margin:0;"><input name="adsense_slot_ids[]" value="{{ $slots[$loc] ?? '' }}" placeholder="Slot ID"></div>

                </div>

            @endforeach

        </div>



        <div class="service-block">

            <h3>Google Search Console</h3>

            <label class="checkbox-label" style="margin-bottom:14px;"><input type="checkbox" name="search_console_enabled" value="1" @checked($i('search_console')?->enabled)> Enable Verification</label>

            <div class="form-group"><label>Verification Code</label><input name="search_console_verification_code" value="{{ $i('search_console')?->config['verification_code'] ?? '' }}"></div>

        </div>



        <div class="service-block">

            <h3>Google reCAPTCHA v3</h3>

            <label class="checkbox-label" style="margin-bottom:14px;"><input type="checkbox" name="recaptcha_enabled" value="1" @checked($i('recaptcha')?->enabled)> Enable reCAPTCHA</label>

            <div class="form-grid">

                <div class="form-group"><label>Site Key</label><input name="recaptcha_site_key" value="{{ $i('recaptcha')?->config['site_key'] ?? '' }}"></div>

                <div class="form-group"><label>Secret Key</label><input name="recaptcha_secret_key" value="{{ $i('recaptcha')?->config['secret_key'] ?? '' }}"></div>

            </div>

        </div>



        <div class="service-block">

            <h3>Google Maps</h3>

            <label class="checkbox-label" style="margin-bottom:14px;"><input type="checkbox" name="maps_enabled" value="1" @checked($i('maps')?->enabled)> Enable Maps on Contact Page</label>

            <div class="form-group"><label>Embed URL</label><textarea name="maps_embed_url" rows="3" placeholder="https://www.google.com/maps/embed?pb=...">{{ $i('maps')?->config['embed_url'] ?? '' }}</textarea></div>

            <div class="form-grid">

                <div class="form-group"><label>Latitude</label><input name="maps_lat" value="{{ $i('maps')?->config['lat'] ?? '' }}"></div>

                <div class="form-group"><label>Longitude</label><input name="maps_lng" value="{{ $i('maps')?->config['lng'] ?? '' }}"></div>

                <div class="form-group"><label>Zoom</label><input name="maps_zoom" type="number" value="{{ $i('maps')?->config['zoom'] ?? 14 }}"></div>

            </div>

        </div>



        <div class="service-block">

            <h3>Google Ads Conversion</h3>

            <label class="checkbox-label" style="margin-bottom:14px;"><input type="checkbox" name="google_ads_enabled" value="1" @checked($i('google_ads')?->enabled)> Enable Conversion Tracking</label>

            <div class="form-grid">

                <div class="form-group"><label>Conversion ID</label><input name="google_ads_conversion_id" value="{{ $i('google_ads')?->config['conversion_id'] ?? '' }}"></div>

                <div class="form-group"><label>Conversion Label</label><input name="google_ads_conversion_label" value="{{ $i('google_ads')?->config['conversion_label'] ?? '' }}"></div>

            </div>

        </div>



        <div class="form-actions">

            <button class="btn btn-primary">Save Google Services</button>

        </div>

    </form>

</div>

@endsection

