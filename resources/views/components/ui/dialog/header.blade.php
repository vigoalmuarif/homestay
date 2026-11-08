@use('App\Support\TwMerge')
<div data-slot="dialog-header" {{ $attributes->except('class') }} class="{{ TwMerge::merge('flex flex-col gap-2', $attributes->get('class')) }}">{{ $slot }}</div>
