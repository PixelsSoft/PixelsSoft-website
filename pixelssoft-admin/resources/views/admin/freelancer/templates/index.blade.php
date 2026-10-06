@extends('admin.layout')

@section('title', 'Bid Templates')

@section('content')
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
<div class="page-header" style="margin-bottom:1rem;"><h1 style="margin:0;">Bid Templates</h1></div>

<div class="card" style="margin-bottom:1rem;">
    <div class="card-header"><h2 style="margin:0;">New Template</h2></div>
    <div class="card-body">
        <form method="post" action="{{ route('admin.freelancer.templates.store') }}" class="admin-form admin-form-medium">
            @csrf
            <label>Name<input name="name" class="form-control" required></label>
            <label>Content
                <textarea name="content" rows="12" class="form-control" required>Hello @{{client_name}},

I reviewed your project "@{{project_title}}" and can help with @{{matched_skills}}.

I can deliver within @{{delivery_days}} days.

Relevant work:
@{{portfolio}}

Best regards,
@{{freelancer_name}}</textarea>
            </label>
            <p class="text-muted">Variables: @{{project_title}} @{{client_name}} @{{matched_skills}} @{{budget}} @{{delivery_days}} @{{portfolio}} @{{freelancer_name}}</p>
            <label class="form-check"><input type="checkbox" name="is_active" value="1" checked> Active</label>
            <button class="btn btn-primary" type="submit">Save Template</button>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-wrap">
        <table>
            <thead><tr><th>Name</th><th>Active</th><th></th></tr></thead>
            <tbody>
                @forelse($templates as $template)
                    <tr>
                        <td>{{ $template->name }}</td>
                        <td>{{ $template->is_active ? 'Yes' : 'No' }}</td>
                        <td>
                            <form method="post" action="{{ route('admin.freelancer.templates.destroy', $template) }}">@csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline" type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="empty-state">No templates yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
