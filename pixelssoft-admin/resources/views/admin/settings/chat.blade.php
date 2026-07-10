@extends('admin.layout')

@section('title', 'Chat Support')

@section('content')

<div class="card">

    <div class="card-header"><h2>Tawk.to Live Chat</h2></div>

    <p style="color:#6b7280;margin-bottom:20px;font-size:14px;">Configure the chat widget that appears on your website. Changes sync via the settings API.</p>

    @include('admin.partials.errors')

    <form method="POST" action="{{ route('admin.settings.update') }}">

        @csrf

        <div class="form-grid">

            <div class="form-group">

                <label>Tawk.to Property ID</label>

                <input name="tawk_property_id" value="{{ $settings['tawk_property_id'] ?? '648864e494cf5d49dc5d6a94' }}">

            </div>

            <div class="form-group">

                <label>Tawk.to Widget ID</label>

                <input name="tawk_widget_id" value="{{ $settings['tawk_widget_id'] ?? '1h2qck7tf' }}">

            </div>

        </div>

        <p class="hint">Find these IDs in your <a href="https://dashboard.tawk.to" target="_blank">Tawk.to dashboard</a> under Administration → Channels → Chat Widget.</p>

        <div class="form-actions"><button class="btn btn-primary">Save Chat Settings</button></div>

    </form>

</div>

@endsection

