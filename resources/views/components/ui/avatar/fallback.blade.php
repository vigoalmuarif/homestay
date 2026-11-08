@use('App\Support\TwMerge')
@php
    $classes = TwMerge::merge(
        'flex size-full items-center justify-center rounded-full bg-[#F3F4F6] text-sm text-gray-700 group-data-[size=sm]/avatar:text-xs group-data-[size=xs]/avatar:text-xs dark:bg-[rgba(255,255,255,0.05)] dark:text-gray-400',
        $attributes->get('class')
    );
@endphp
<template x-if="!loaded">
<span data-slot="avatar-fallback" {{ $attributes->except('class') }} class="{{ $classes }}">{{ $slot }}</span>
</template>
