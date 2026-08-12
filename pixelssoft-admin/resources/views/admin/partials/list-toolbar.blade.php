{{--
  Shared list filter toolbar.
  Usage:
  @include('admin.partials.list-toolbar', [
    'showSearch' => true,
    'searchPlaceholder' => 'Search…',
    'filters' => [
      ['name' => 'status', 'label' => 'All statuses', 'options' => ['new' => 'New']],
    ],
  ])
--}}
@php
    $action = $action ?? url()->current();
    $showSearch = $showSearch ?? true;
    $searchPlaceholder = $searchPlaceholder ?? 'Search…';
    $filters = $filters ?? [];
    $filterNames = collect($filters)->pluck('name')->all();
    $hasActive = request()->filled('q') || collect($filterNames)->contains(fn ($name) => request()->filled($name));
@endphp
<form method="GET" action="{{ $action }}" class="list-toolbar">
    <div class="list-toolbar-filters">
        @if($showSearch)
            <input
                type="search"
                name="q"
                value="{{ request('q') }}"
                placeholder="{{ $searchPlaceholder }}"
                class="list-toolbar-input"
            >
        @endif

        @foreach($filters as $filter)
            <select name="{{ $filter['name'] }}" class="list-toolbar-select">
                <option value="">{{ $filter['label'] }}</option>
                @foreach(($filter['options'] ?? []) as $value => $label)
                    <option value="{{ $value }}" @selected((string) request($filter['name']) === (string) $value)>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        @endforeach

        <button type="submit" class="btn btn-sm btn-outline">Filter</button>
        @if($hasActive)
            <a href="{{ $action }}" class="btn btn-sm btn-outline">Reset</a>
        @endif
    </div>
    @isset($actions)
        <div class="list-toolbar-actions">
            {!! $actions !!}
        </div>
    @endisset
</form>
