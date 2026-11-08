@use('App\Support\TwMerge')
@php
    $classes = TwMerge::merge(
        'flex items-center gap-0.5',
        $attributes->get('class')
    );
@endphp
<ul data-slot="pagination-content" {{ $attributes->except('class') }} class="{{ $classes }}">{{ $slot }}</ul>
