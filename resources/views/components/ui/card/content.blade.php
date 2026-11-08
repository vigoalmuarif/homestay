@use('App\Support\TwMerge')
@php
    $classes = TwMerge::merge(
        
        $attributes->get('class')
    );
@endphp
<div data-slot="card-content" {{ $attributes->except('class') }} class="{{ $classes }}">{{ $slot }}</div>
