@use('App\Support\TwMerge')
@php
    $classes = TwMerge::merge(
        'flex size-8 items-center justify-center dark:text-white/90 [&_svg:not([class*=size-])]:size-4',
        $attributes->get('class')
    );
@endphp
<span aria-hidden="true" data-slot="pagination-ellipsis" {{ $attributes->except('class') }} class="{{ $classes }}">@if ($slot->isEmpty())<x-svg.horizontal-dots />@else{{ $slot }}@endif<span class="sr-only">More pages</span></span>
