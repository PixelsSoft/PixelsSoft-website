@extends('admin.layout')

@section('title', 'Deal Pipeline')

@section('content')
<div class="card">
    <div class="card-header">
        <h2>{{ $pipeline?->name ?? 'Pipeline' }}</h2>
        @can('crm.deals.create')
            <a href="{{ route('admin.crm.deals.create') }}" class="btn btn-primary">+ New Deal</a>
        @endcan
    </div>

    @if(!$pipeline)
        <div class="empty-state"><p>No pipeline found. Run <code>php artisan db:seed --class=CrmSeeder</code></p></div>
    @else
        <div class="kanban-board">
            @foreach($pipeline->stages as $stage)
                <div class="kanban-column" data-stage-id="{{ $stage->id }}">
                    <div class="kanban-column-header" style="border-top:3px solid {{ $stage->color }}">
                        <strong>{{ $stage->name }}</strong>
                        <span class="badge badge-draft">{{ $stage->deals->count() }}</span>
                    </div>
                    <div class="kanban-cards" data-stage-id="{{ $stage->id }}">
                        @foreach($stage->deals as $deal)
                            <div class="kanban-card" draggable="true" data-deal-id="{{ $deal->id }}">
                                <strong>{{ $deal->title }}</strong>
                                @if($deal->company)<small>{{ $deal->company->name }}</small>@endif
                                <div class="kanban-card-meta">
                                    <span>${{ number_format($deal->value, 0) }}</span>
                                    @can('crm.deals.edit')
                                        <a href="{{ route('admin.crm.deals.edit', $deal) }}">Edit</a>
                                    @endcan
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

@can('crm.deals.edit')
<script>
document.querySelectorAll('.kanban-card').forEach(card => {
    card.addEventListener('dragstart', e => {
        e.dataTransfer.setData('deal-id', card.dataset.dealId);
        card.classList.add('dragging');
    });
    card.addEventListener('dragend', () => card.classList.remove('dragging'));
});

document.querySelectorAll('.kanban-cards').forEach(column => {
    column.addEventListener('dragover', e => { e.preventDefault(); column.classList.add('drag-over'); });
    column.addEventListener('dragleave', () => column.classList.remove('drag-over'));
    column.addEventListener('drop', async e => {
        e.preventDefault();
        column.classList.remove('drag-over');
        const dealId = e.dataTransfer.getData('deal-id');
        const stageId = column.dataset.stageId;
        const token = '{{ csrf_token() }}';
        await fetch(`/admin/crm/deals/${dealId}/stage`, {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
            body: JSON.stringify({ stage_id: stageId })
        });
        location.reload();
    });
});
</script>
@endcan
@endsection
