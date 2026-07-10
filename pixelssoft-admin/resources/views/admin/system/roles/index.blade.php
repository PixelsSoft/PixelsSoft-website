@extends('admin.layout')

@section('title', 'Roles & Permissions')

@section('content')
<div class="card">
    <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;">
        <h2>Roles</h2>
        @can('system.roles.edit')
            <a href="{{ route('admin.system.roles.create') }}" class="btn btn-primary btn-sm">+ New Role</a>
        @endcan
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr><th>Role</th><th>Users</th><th>Permissions</th><th>Actions</th></tr>
            </thead>
            <tbody>
                @foreach($roles as $role)
                    <tr>
                        <td><strong>{{ str_replace('-', ' ', ucwords($role->name, '-')) }}</strong></td>
                        <td>{{ $role->users_count }}</td>
                        <td>{{ $role->permissions_count }}</td>
                        <td>
                            @can('system.roles.edit')
                                <a href="{{ route('admin.system.roles.edit', $role) }}" class="btn btn-sm btn-outline">Edit Permissions</a>
                            @endcan
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
