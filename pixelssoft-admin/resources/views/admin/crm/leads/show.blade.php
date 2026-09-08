@extends('admin.layout')

@section('title', $lead->title)

@section('content')
<div class="card">
    <div class="card-header">
        <h2>{{ $lead->title }}</h2>
        <div>
            @can('crm.leads.edit')
                <a href="{{ route('admin.crm.leads.edit', $lead) }}" class="btn btn-sm btn-outline">Edit</a>
            @endcan
            @can('crm.leads.convert')
                @if($lead->status !== 'converted')
                    <form action="{{ route('admin.crm.leads.convert', $lead) }}" method="POST" style="display:inline">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-primary">Convert to Deal</button>
                    </form>
                @endif
            @endcan
        </div>
    </div>
    <div class="form-grid" style="padding:0 24px 24px">
        <div><strong>Status:</strong> {{ $lead->status }}</div>
        <div><strong>Score:</strong> {{ $lead->score }}</div>
        <div><strong>Company:</strong> {{ $lead->company?->name ?? '—' }}</div>
        <div><strong>Budget:</strong> {{ $lead->currency ?: 'USD' }} {{ number_format((float) $lead->budget, 2) }}</div>
        <div><strong>Owner:</strong> {{ $lead->owner?->name ?? '—' }}</div>
        <div><strong>Source:</strong> {{ $lead->acquisitionSource?->name ?? $lead->source ?? '—' }}</div>
        <div><strong>Portal ID:</strong> {{ $lead->portal_contract_id ?? '—' }}</div>
        <div><strong>Portal URL:</strong>
            @if($lead->portal_url)
                <a href="{{ $lead->portal_url }}" target="_blank" rel="noopener">Open job</a>
            @else
                —
            @endif
        </div>
    </div>
    @if($lead->notes)
        <div style="padding:0 24px 24px"><strong>Notes:</strong><p style="margin-top:8px;color:#6b7280">{{ $lead->notes }}</p></div>
    @endif
</div>

@include('admin.crm.partials.activity-timeline', ['activities' => $lead->activities, 'relatedType' => 'lead', 'relatedId' => $lead->id])
@endsection
