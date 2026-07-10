@extends('admin.layout')

@section('title', 'My Profile')

@section('content')
<div class="card">
    <div class="card-header"><h2>Profile Settings</h2></div>
    <form method="POST" action="{{ route('admin.system.profile.update') }}" enctype="multipart/form-data">
        @csrf @method('PUT')
        <div class="form-grid">
            <div class="form-group">
                <label>Name *</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" value="{{ $user->email }}" disabled>
            </div>
            <div class="form-group">
                <label>Phone</label>
                <input type="text" name="phone" value="{{ old('phone', $user->phone) }}">
            </div>
            <div class="form-group">
                <label>Avatar</label>
                <input type="file" name="avatar" accept="image/*">
                @if($user->avatar)
                    <img src="{{ str_starts_with($user->avatar, 'http') ? $user->avatar : asset(ltrim($user->avatar, '/')) }}" alt="Avatar" style="margin-top:8px;width:48px;height:48px;border-radius:50%;object-fit:cover">
                @endif
            </div>
            <div class="form-group">
                <label>New Password</label>
                <input type="password" name="password">
            </div>
            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" name="password_confirmation">
            </div>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Save Profile</button>
        </div>
    </form>
</div>
@endsection
