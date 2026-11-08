@props(['videoId', 'aspectRatio' => '16:9', 'title' => 'YouTube video'])
@php
    $aspect = ['16:9' => 'aspect-video', '4:3' => 'aspect-4/3', '21:9' => 'aspect-21/9', '1:1' => 'aspect-square'][$aspectRatio] ?? 'aspect-video';
@endphp
<div {{ $attributes->except('class') }} class="overflow-hidden rounded-lg {{ $aspect }} {{ html_entity_decode((string) $attributes->get('class'), ENT_QUOTES) }}">
    <iframe src="https://www.youtube.com/embed/{{ $videoId }}" title="{{ $title }}" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture;" allowfullscreen class="h-full w-full"></iframe>
</div>
