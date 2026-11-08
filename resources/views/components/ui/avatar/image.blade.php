@use('App\Support\TwMerge')
<img data-slot="avatar-image" x-init="loaded = $el.complete && $el.naturalWidth > 0" x-on:load="loaded = true" x-on:error="loaded = false" {{ $attributes->except('class') }} class="{{ TwMerge::merge('aspect-square size-full rounded-full object-cover', $attributes->get('class')) }}">
