@use('App\Support\TwMerge')
@php
    $classes = TwMerge::merge(
        'mx-auto flex w-full justify-center',
        $attributes->get('class')
    );
@endphp
<nav role="navigation" aria-label="pagination" data-slot="pagination" {{ $attributes->except('class') }} class="{{ $classes }}">{{ $slot }}</nav>
