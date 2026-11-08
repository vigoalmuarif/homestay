import { CALENDAR_VIEW_OPTIONS } from './types';

// Renders into the last toolbar section.
export function renderViewSelector(containerEl, currentView, onViewChange) {
    if (!containerEl) return;

    const activeOption =
        CALENDAR_VIEW_OPTIONS.find((v) => v.key === currentView) ||
        CALENDAR_VIEW_OPTIONS.find((v) => v.key === 'dayGridMonth') ||
        CALENDAR_VIEW_OPTIONS[1];

    containerEl.innerHTML = `
        <div class="calendar-view-dropdown relative">
            <button
                type="button"
                class="calendar-view-btn flex h-9 w-full min-w-18 items-center justify-center gap-1 rounded-lg border border-gray-300 ps-2.5 pe-1.5 text-xs font-medium text-gray-700 shadow-xs sm:min-w-20 sm:gap-1.5 sm:ps-3 sm:pe-2 sm:text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400"
                aria-expanded="false"
                aria-haspopup="listbox"
            >
                <span class="calendar-view-label">${activeOption.label}</span>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="calendar-view-chevron h-4 w-4 transition-transform duration-200 sm:h-4.5 sm:w-4.5">
                    <path d="m6 9 6 6 6-6" />
                </svg>
            </button>
            <div class="calendar-view-menu absolute end-0 z-50 mt-1.5 hidden w-36 max-w-[calc(100vw-32px)] space-y-0.5 rounded-xl border border-gray-200 bg-white p-1.5 shadow-lg sm:w-38 dark:border-gray-700 dark:bg-gray-900">
                ${CALENDAR_VIEW_OPTIONS.map(
                    (view) => `
                    <button
                        type="button"
                        data-view-key="${view.key}"
                        class="calendar-view-option w-full rounded-lg px-2.5 py-1.5 text-start text-xs text-gray-700 hover:bg-gray-100 sm:text-sm dark:text-gray-300 dark:hover:bg-white/5 ${currentView === view.key ? 'bg-gray-100 font-medium dark:bg-white/5' : 'font-normal'}"
                    >
                        ${view.label}
                    </button>`,
                ).join('')}
            </div>
        </div>`;

    const dropdown = containerEl.querySelector('.calendar-view-dropdown');
    const btn = dropdown.querySelector('.calendar-view-btn');
    const menu = dropdown.querySelector('.calendar-view-menu');
    const chevron = dropdown.querySelector('.calendar-view-chevron');

    const setOpen = (open) => {
        menu.classList.toggle('hidden', !open);
        chevron.classList.toggle('rotate-180', open);
        btn.setAttribute('aria-expanded', open ? 'true' : 'false');
    };

    btn.addEventListener('click', (e) => {
        e.stopPropagation();
        setOpen(menu.classList.contains('hidden'));
    });

    dropdown.querySelectorAll('.calendar-view-option').forEach((option) => {
        option.addEventListener('click', (e) => {
            e.stopPropagation();
            onViewChange(option.dataset.viewKey);
            setOpen(false);
        });
    });

    // Close on outside click (window listener is replaced on every render)
    if (containerEl._outsideClick) window.removeEventListener('click', containerEl._outsideClick);
    containerEl._outsideClick = (event) => {
        if (!dropdown.contains(event.target)) setOpen(false);
    };
    window.addEventListener('click', containerEl._outsideClick);
}
