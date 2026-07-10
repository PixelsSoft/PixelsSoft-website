@extends('admin.layout')

@section('title', 'Edit Role')

@section('content')
<div class="card">
    <div class="card-header">
        <h2>{{ str_replace('-', ' ', ucwords($role->name, '-')) }} — Permissions</h2>
    </div>

    @if($role->name === 'super-admin')
        <div class="alert alert-error">Super Admin has all permissions and cannot be modified.</div>
    @else
        <form method="POST" action="{{ route('admin.system.roles.update', $role) }}">
            @csrf @method('PUT')

            @foreach($grouped as $module => $permissions)
                <div class="permission-module">
                    <h3>{{ strtoupper($module) }}</h3>
                    <div class="permission-grid">
                        @foreach($permissions as $permission)
                            <label class="permission-check">
                                <input type="checkbox" name="permissions[]" value="{{ $permission }}"
                                    @checked(in_array($permission, old('permissions', $assigned)))>
                                {{ str_replace($module.'.', '', $permission) }}
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach

            <div class="form-actions">
                <a href="{{ route('admin.system.roles.index') }}" class="btn btn-outline">Cancel</a>
                <button type="submit" class="btn btn-primary">Save Permissions</button>
            </div>
        </form>
    @endif
</div>
@endsection
