<div class="card">
    <div class="card-header"><h2>Activities</h2></div>

    @can('crm.activities.create')
        <form method="POST" action="{{ route('admin.crm.activities.store') }}" style="padding:0 24px 24px;border-bottom:1px solid var(--border)">
            @csrf
            <input type="hidden" name="related_type" value="{{ $relatedType }}">
            <input type="hidden" name="related_id" value="{{ $relatedId }}">
            <div class="form-grid">
                <div class="form-group">
                    <label>Type</label>
                    <select name="type">
                        @foreach(['call','email','meeting','note','task'] as $type)
                            <option value="{{ $type }}">{{ ucfirst($type) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group"><label>Subject *</label><input type="text" name="subject" required></div>
                <div class="form-group"><label>Due Date</label><input type="datetime-local" name="due_at"></div>
            </div>
            <div class="form-group" style="margin-top:12px"><label>Notes</label><textarea name="body" rows="2"></textarea></div>
            <button type="submit" class="btn btn-sm btn-primary" style="margin-top:12px">Add Activity</button>
        </form>
    @endcan

    <div style="padding:24px">
        @forelse($activities as $activity)
            <div class="activity-item {{ $activity->isCompleted() ? 'completed' : '' }}">
                <div class="activity-type">{{ ucfirst($activity->type) }}</div>
                <div>
                    <strong>{{ $activity->subject }}</strong>
                    @if($activity->body)<p style="color:#6b7280;font-size:13px;margin-top:4px">{{ $activity->body }}</p>@endif
                    <small style="color:#9ca3af">{{ $activity->user?->name }} · {{ $activity->created_at->format('M d, Y H:i') }}</small>
                </div>
                @can('crm.activities.edit')
                    @if(!$activity->isCompleted())
                        <form action="{{ route('admin.crm.activities.complete', $activity) }}" method="POST">
                            @csrf @method('PATCH')
                            <button type="submit" class="btn btn-sm btn-outline">Done</button>
                        </form>
                    @endif
                @endcan
            </div>
        @empty
            <p class="empty-state">No activities yet.</p>
        @endforelse
    </div>
</div>
