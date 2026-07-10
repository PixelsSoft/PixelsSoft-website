@extends('admin.layout')

@section('title', 'Create Role')

@section('content')
<div class="card">
    <div class="card-header">
        <h2>Create Role</h2>
        <a href="{{ route('admin.system.roles.index') }}" class="btn btn-sm btn-outline">Back</a>
    </div>
    <form method="POST" action="{{ route('admin.system.roles.store') }}" style="padding:24px">
        @csrf
        <div class="form-group" style="max-width:400px;margin-bottom:24px">
            <label>Role name *</label>
            <input type="text" name="name" value="{{ old('name') }}" placeholder="e.g. account-manager" required pattern="[a-z0-9]+(-[a-z0-9]+)*">
            <small style="color:#6b7280">Lowercase letters, numbers, and hyphens only.</small>
            @error('name')<small style="color:var(--danger)">{{ $message }}</small>@enderror
        </div>

        <h3 style="margin-bottom:16px;font-size:15px">Permissions</h3>
        <div class="permission-grid">
            @foreach($grouped as $module => $permissions)
                <div class="permission-group">
                    <h4>{{ strtoupper($module) }}</h4>
                    @foreach($permissions as $permission)
                        <label class="permission-check">
                            <input type="checkbox" name="permissions[]" value="{{ $permission }}" @checked(in_array($permission, old('permissions', [])))>
                            {{ $permission }}
                        </label>
                    @endforeach
                </div>
            @endforeach
        </div>

        <div class="form-actions" style="margin-top:24px">
            <a href="{{ route('admin.system.roles.index') }}" class="btn btn-outline">Cancel</a>
            <button type="submit" class="btn btn-primary">Create Role</button>
        </div>
    </form>
</div>
@endsection
