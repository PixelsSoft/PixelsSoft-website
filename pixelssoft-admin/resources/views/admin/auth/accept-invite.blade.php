<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accept Invitation — PixelsSoft</title>
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
</head>
<body class="login-page">
<div class="login-card">
    <div class="login-brand">
        <h1>Pixels<span>Soft</span></h1>
        <p>Accept your invitation</p>
    </div>

    @if(session('error'))
        <div class="alert alert-error">{{ session('error') }}</div>
    @endif

    @if(!$invitation)
        <div class="alert alert-error">This invitation link is invalid or has expired.</div>
        <a href="{{ route('admin.login') }}" class="btn btn-primary" style="width:100%;text-align:center">Go to Login</a>
    @else
        <form method="POST" action="{{ route('invite.accept.submit', $token) }}">
            @csrf
            <div class="form-group">
                <label>Email</label>
                <input type="email" value="{{ $invitation->email }}" disabled>
            </div>
            <div class="form-group">
                <label>Your Name *</label>
                <input type="text" name="name" value="{{ old('name', $invitation->name) }}" required>
            </div>
            <div class="form-group">
                <label>Password *</label>
                <input type="password" name="password" required>
            </div>
            <div class="form-group">
                <label>Confirm Password *</label>
                <input type="password" name="password_confirmation" required>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%">Create Account</button>
        </form>
    @endif
</div>
</body>
</html>
