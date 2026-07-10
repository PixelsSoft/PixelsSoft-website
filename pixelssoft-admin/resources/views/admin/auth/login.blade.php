<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — PixelsSoft Admin</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.png') }}">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
</head>
<body>
<div class="login-page">
    <div class="login-left">
        <h1>Manage your<br><span>PixelsSoft</span> website</h1>
        <p>Control blogs, portfolio, showcase slides, services, contact messages, and Google integrations — all from one place.</p>
        <div class="login-features">
            <div class="login-feature"><span class="dot"></span> Content syncs to your live website via API</div>
            <div class="login-feature"><span class="dot"></span> Manage SEO, chat, and analytics settings</div>
            <div class="login-feature"><span class="dot"></span> Upload and organize media assets</div>
        </div>
    </div>
    <div class="login-right">
        <div class="login-card">
            <h2>Welcome back</h2>
            <p class="subtitle">Sign in to the admin panel</p>
            @if($errors->any())
                <div class="alert alert-error">{{ $errors->first() }}</div>
            @endif
            <form method="POST" action="{{ route('admin.login.submit') }}">
                @csrf
                <div class="form-group">
                    <label>Email address</label>
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="admin@pixelssoft.com" required autofocus>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" placeholder="Enter your password" required>
                </div>
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="remember"> Remember me
                    </label>
                </div>
                <button type="submit" class="btn btn-accent" style="width:100%;padding:12px;margin-top:8px;">Sign In</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
