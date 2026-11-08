@use('App\Support\Ui\AlertVariants')
@props(['variant' => 'default'])
<div data-slot="alert" role="alert" {{ $attributes->except('class') }} class="{{ AlertVariants::classes($variant, $attributes->get('class')) }}">{{ $slot }}</div>
