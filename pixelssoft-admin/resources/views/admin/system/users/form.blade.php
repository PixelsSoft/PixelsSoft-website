@extends('admin.layout')

@section('title', $user->exists ? 'Edit User' : 'New User')

@section('content')
<div class="card">
    <div class="card-header">
        <h2>{{ $user->exists ? 'Edit User' : 'Create User' }}</h2>
    </div>
    <form method="POST" action="{{ $user->exists ? route('admin.system.users.update', $user) : route('admin.system.users.store') }}">
        @csrf
        @if($user->exists) @method('PUT') @endif

        <div class="form-grid">
            <div class="form-group">
                <label>Name *</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required>
            </div>
            <div class="form-group">
                <label>Email *</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" required>
            </div>
            <div class="form-group">
                <label>Phone</label>
                <input type="text" name="phone" value="{{ old('phone', $user->phone) }}">
            </div>
            <div class="form-group">
                <label>Status *</label>
                <select name="status" required>
                    <option value="active" @selected(old('status', $user->status ?? 'active') === 'active')>Active</option>
                    <option value="inactive" @selected(old('status', $user->status) === 'inactive')>Inactive</option>
                </select>
            </div>
            <div class="form-group">
                <label>{{ $user->exists ? 'New Password' : 'Password *' }}</label>
                <input type="password" name="password" {{ $user->exists ? '' : 'required' }}>
            </div>
            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" name="password_confirmation">
            </div>
        </div>

        <div class="form-group" style="margin-top:20px">
            <label>Roles</label>
            <div class="permission-grid">
                @foreach($roles as $role)
                    <label class="permission-check">
                        <input type="checkbox" name="roles[]" value="{{ $role->name }}"
                            @checked(in_array($role->name, old('roles', $user->roles->pluck('name')->all())))>
                        {{ str_replace('-', ' ', ucwords($role->name, '-')) }}
                    </label>
                @endforeach
            </div>
        </div>

        <div class="form-actions">
            <a href="{{ route('admin.system.users.index') }}" class="btn btn-outline">Cancel</a>
            <button type="submit" class="btn btn-primary">{{ $user->exists ? 'Update' : 'Create' }} User</button>
        </div>
    </form>
</div>
@endsection
