@extends('admin.layout')
@section('title', 'Edit Section')
@section('content')
<h1>Edit {{ $section->page_key }} / {{ $section->section_key }}</h1>
<div class="card"><form method="POST" action="{{ route('admin.sections.update', $section) }}">@csrf @method('PUT')
<div class="form-group"><label>Content JSON</label><textarea name="content_json" rows="20">{{ json_encode($section->content, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</textarea></div>
<button class="btn btn-primary">Save</button></form></div>
@endsection
