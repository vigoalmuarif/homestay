@use('Illuminate\Support\Js')
@use('App\Support\Ui\ButtonVariants')
@props(['currentPage' => 1, 'totalPages' => 10])
@php
    $disabledClass = 'pointer-events-none cursor-not-allowed opacity-50';
    $classes = [
        'link' => ButtonVariants::classes('outline', 'icon'),
        'linkActive' => ButtonVariants::classes('default', 'icon'),
        'nav' => ButtonVariants::classes('outline', 'icon'),
        'navDisabled' => ButtonVariants::classes('outline', 'icon', $disabledClass),
    ];
@endphp
<div
    x-data="{
        currentPage: {{ (int) $currentPage }},
        totalPages: {{ (int) $totalPages }},
        classes: {{ Js::from($classes) }},
        get pages() {
            const total = this.totalPages;
            const current = this.currentPage;
            if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1);
            if (current <= 4) return [1, 2, 3, 4, 5, 'ellipsis-end', total];
            if (current >= total - 3) return [1, 'ellipsis-start', total - 4, total - 3, total - 2, total - 1, total];
            return [1, 'ellipsis-start', current - 1, current, current + 1, 'ellipsis-end', total];
        },
        goPrevious() { if (this.currentPage > 1) this.currentPage -= 1; },
        goNext() { if (this.currentPage < this.totalPages) this.currentPage += 1; },
        prevClass() { return this.currentPage <= 1 ? this.classes.navDisabled : this.classes.nav; },
        nextClass() { return this.currentPage >= this.totalPages ? this.classes.navDisabled : this.classes.nav; },
    }"
    {{ $attributes->merge(['class' => 'border-t-0 px-5 py-4 sm:px-6']) }}
>
    <!-- Mobile Pagination (< sm) -->
    <div class="flex w-full items-center justify-between sm:hidden">
        <span class="text-sm font-medium text-gray-700 dark:text-gray-400">
            <span x-text="currentPage"></span>/<span x-text="totalPages"></span>
        </span>

        <div class="flex items-center gap-2">
            <a href="#" aria-label="Go to previous page" data-slot="pagination-link" :class="prevClass()" :aria-disabled="currentPage <= 1 ? 'true' : null" @click.prevent="goPrevious()">
                <x-svg.chevron-left data-icon="inline-start" class="rtl:rotate-180" />
            </a>
            <a href="#" aria-label="Go to next page" data-slot="pagination-link" :class="nextClass()" :aria-disabled="currentPage >= totalPages ? 'true' : null" @click.prevent="goNext()">
                <x-svg.chevron-right data-icon="inline-end" class="rtl:rotate-180" />
            </a>
        </div>
    </div>

    <!-- Desktop Pagination (>= sm) -->
    <div class="hidden sm:flex sm:items-center sm:justify-start">
        <x-ui.pagination class="mx-0 w-auto justify-start">
            <x-ui.pagination.content class="gap-1.5">
                <x-ui.pagination.item>
                    <a href="#" aria-label="Go to previous page" data-slot="pagination-link" :class="prevClass()" :aria-disabled="currentPage <= 1 ? 'true' : null" @click.prevent="goPrevious()">
                        <x-svg.chevron-left data-icon="inline-start" class="rtl:rotate-180" />
                    </a>
                </x-ui.pagination.item>

                <template x-for="(item, index) in pages" :key="typeof item === 'number' ? item : `ellipsis-${index}`">
                    <x-ui.pagination.item>
                        <template x-if="typeof item === 'string'">
                            <x-ui.pagination.ellipsis />
                        </template>
                        <template x-if="typeof item === 'number'">
                            <a href="#" data-slot="pagination-link" :data-active="currentPage === item ? 'true' : 'false'" :aria-current="currentPage === item ? 'page' : null" :class="currentPage === item ? classes.linkActive : classes.link" @click.prevent="currentPage = item" x-text="item"></a>
                        </template>
                    </x-ui.pagination.item>
                </template>

                <x-ui.pagination.item>
                    <a href="#" aria-label="Go to next page" data-slot="pagination-link" :class="nextClass()" :aria-disabled="currentPage >= totalPages ? 'true' : null" @click.prevent="goNext()">
                        <x-svg.chevron-right data-icon="inline-end" class="rtl:rotate-180" />
                    </a>
                </x-ui.pagination.item>
            </x-ui.pagination.content>
        </x-ui.pagination>
    </div>
</div>
