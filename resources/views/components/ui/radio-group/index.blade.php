@use('App\Support\TwMerge')
@props(['defaultValue' => null, 'name' => null])
<div role="radiogroup" data-slot="radio-group" style="outline: none;"
    x-data="{ value: @js($defaultValue) }" {{ $attributes->except('class') }} class="{{ TwMerge::merge('grid w-full gap-2', $attributes->get('class')) }}">{{ $slot }}</div>
