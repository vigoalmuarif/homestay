@use('App\Support\TwMerge')
{{-- `overlay` renders an overlay vertical scrollbar (track + thumb on top of the content) instead of the native one, so it takes no layout space. --}}
@props(['viewportClass' => null, 'overlay' => false])
@php
    $viewportClasses = TwMerge::merge(
        'custom-scrollbar size-full overflow-x-hidden overflow-y-auto rounded-[inherit] outline-none transition-[color,box-shadow] focus-visible:ring-[3px] focus-visible:ring-brand-500/20 focus-visible:outline-1',
        $overlay ? '[scrollbar-width:none] [&::-webkit-scrollbar]:hidden!' : null,
        $viewportClass
    );
@endphp
@if ($overlay)
<div
    data-slot="scroll-area"
    x-data="{
        show: false,
        thumbHeight: 0,
        thumbTop: 0,
        update() {
            const el = this.$refs.viewport;
            if (!el) return;
            const { scrollTop, scrollHeight, clientHeight } = el;
            this.show = scrollHeight > clientHeight + 1;
            if (!this.show) return;
            const trackHeight = clientHeight;
            this.thumbHeight = Math.max(18, (clientHeight / scrollHeight) * trackHeight);
            this.thumbTop = (scrollTop / (scrollHeight - clientHeight)) * (trackHeight - this.thumbHeight);
        },
        startDrag(e) {
            const el = this.$refs.viewport;
            const startY = e.clientY;
            const startScroll = el.scrollTop;
            const ratio = (el.scrollHeight - el.clientHeight) / (el.clientHeight - this.thumbHeight);
            const move = (ev) => { el.scrollTop = startScroll + (ev.clientY - startY) * ratio; };
            const up = () => { window.removeEventListener('pointermove', move); window.removeEventListener('pointerup', up); };
            window.addEventListener('pointermove', move);
            window.addEventListener('pointerup', up);
        },
    }"
    x-init="$nextTick(() => update()); new ResizeObserver(() => update()).observe($refs.viewport); new ResizeObserver(() => update()).observe($refs.content)"
    {{ $attributes->except('class') }}
    class="{{ TwMerge::merge('relative', $attributes->get('class')) }}"
>
    <div data-slot="scroll-area-viewport" x-ref="viewport" @scroll.passive="update()" class="{{ $viewportClasses }}">
        <div class="min-w-0" x-ref="content">{{ $slot }}</div>
    </div>
    <div
        data-slot="scroll-area-scrollbar"
        data-orientation="vertical"
        x-show="show"
        style="display: none;"
        class="absolute end-0 top-0 flex h-full w-1.5 touch-none rounded-full bg-gray-100 transition-colors select-none dark:bg-gray-800"
    >
        <div
            data-slot="scroll-area-thumb"
            class="absolute start-0 w-full! rounded-full bg-gray-300 transition-colors hover:bg-gray-400 dark:bg-gray-600 dark:hover:bg-gray-500"
            x-bind:style="`height: ${thumbHeight}px; top: ${thumbTop}px`"
            @pointerdown.prevent="startDrag($event)"
        ></div>
    </div>
</div>
@else
<div data-slot="scroll-area" {{ $attributes->except('class') }} class="{{ TwMerge::merge('relative', $attributes->get('class')) }}">
    <div data-slot="scroll-area-viewport" class="{{ $viewportClasses }}">
        <div class="min-w-0">{{ $slot }}</div>
    </div>
</div>
@endif
