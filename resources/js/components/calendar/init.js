import { Calendar } from 'fullcalendar';
import dayGridPlugin from 'fullcalendar/daygrid';
import interactionPlugin from 'fullcalendar/interaction';
import multiMonthPlugin from 'fullcalendar/multimonth';
import themePlugin from 'fullcalendar/themes/classic';
import timeGridPlugin from 'fullcalendar/timegrid';

import 'fullcalendar/skeleton.css';
import 'fullcalendar/themes/classic/palette.css';
import 'fullcalendar/themes/classic/theme.css';

import { renderEventItem } from './event-item';
import { bookmarkSvg, chevronLeftSvg, chevronRightSvg, closeSvg } from './icons';
import { renderViewSelector } from './view-selector';


export function calendarInit() {
    const calendarEl = document.querySelector('#calendar');
    if (!calendarEl) return;

    const containerEl = calendarEl.closest('.custom-calendar');
    const isRtl = document.documentElement.dir === 'rtl';
    const locale = document.documentElement.lang || 'en';
    let isMobileState = window.innerWidth < 640;
    const isMobile = () => isMobileState;
    let currentView = 'dayGridMonth';
    let calendar = null;
    let selectedEvent = null;

    const INITIAL_EVENTS = [
        { id: '1', title: 'Event Conf.', start: new Date().toISOString().split('T')[0], extendedProps: { calendar: 'Danger' } },
        { id: '2', title: 'Meeting', start: new Date(Date.now() + 86400000).toISOString().split('T')[0], extendedProps: { calendar: 'Success' } },
        {
            id: '3',
            title: 'Workshop',
            start: new Date(Date.now() + 172800000).toISOString().split('T')[0],
            end: new Date(Date.now() + 259200000).toISOString().split('T')[0],
            extendedProps: { calendar: 'Primary' },
        },
    ];

    const openModal = (payload) => window.dispatchEvent(new CustomEvent('calendar-open', { detail: payload }));

    const handlers = {
        openAddModal() {
            const d = new Date();
            const day = `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
            selectedEvent = null;
            openModal({ event: null, start: day, end: day });
        },
        dateSelect(selectInfo) {
            const startStr = selectInfo.startStr ? selectInfo.startStr.split('T')[0] : '';
            const endStr = selectInfo.endStr ? selectInfo.endStr.split('T')[0] : startStr;
            selectedEvent = null;
            openModal({ event: null, start: startStr, end: endStr });
        },
        eventClick(clickInfo) {
            const event = clickInfo.event;
            if (event.url) {
                window.location.href = event.url;
                clickInfo.jsEvent?.preventDefault();
                return;
            }
            const startStr = event.startStr ? event.startStr.split('T')[0] : '';
            const endStr = event.endStr ? event.endStr.split('T')[0] : startStr;
            selectedEvent = event;
            openModal({
                event: { id: event.id, title: event.title, level: event.extendedProps?.calendar || 'Primary' },
                start: startStr,
                end: endStr,
            });
        },
        datesSet(arg) {
            currentView = arg.view.type;
            containerEl?.classList.toggle('fc-multimonth', currentView === 'multiMonthYear');
            requestAnimationFrame(() => {
                const chunk = calendarEl.querySelector('.ta-toolbar-section:last-child');
                if (chunk) renderViewSelector(chunk, currentView, (viewKey) => calendar.changeView(viewKey));
            });
        },
    };

    const calendarOptions = {
      plugins: [
        themePlugin,
        dayGridPlugin,
        timeGridPlugin,
        interactionPlugin,
        multiMonthPlugin,
      ],
      initialView: 'dayGridMonth',
      direction: isRtl ? 'rtl' : 'ltr',
      height: 'auto',
      borderless: true,

      headerToolbar: {
        start: 'prev,next addEventButton',
        center: 'title',
        end: '',
      },
      headerToolbarClass:
        'sticky top-0! z-20! bg-white dark:bg-gray-900 flex-wrap! flex-row! items-center justify-between gap-3 sm:gap-4 [padding-inline:16px]! sm:[padding-inline:24px]! pt-4 sm:pt-6 pb-3 sm:pb-4',
      toolbarTitleClass: 'text-base! sm:text-lg! font-semibold! text-gray-800 dark:text-white/90',
      toolbarSectionClass: (info) => {
        if (info.name === 'start') {
          return 'ta-toolbar-section ta-toolbar-start order-2 flex w-full items-center justify-between sm:order-1 sm:w-auto sm:justify-start gap-2'
        }
        if (info.name === 'center') {
          return 'ta-toolbar-section ta-toolbar-center order-1 flex items-center justify-start sm:order-2 sm:justify-center'
        }
        if (info.name === 'end') {
          return 'ta-toolbar-section ta-toolbar-end order-1 flex items-center justify-end sm:order-3 sm:justify-end'
        }
        return 'ta-toolbar-section'
      },
      buttonGroupClass: 'gap-2',
      buttons: {
        prev: {
          iconContent: {
            html: chevronLeftSvg,
          },
          className:
            'flex size-9! sm:size-10! p-0! items-center justify-center! rounded-lg! border! bg-transparent! border-gray-200! text-gray-700 hover:border-gray-200 hover:bg-gray-50! focus:shadow-none active:border-gray-200! active:bg-transparent! active:shadow-none! dark:border-gray-800! dark:text-gray-400 dark:hover:border-gray-800 dark:hover:bg-gray-900! dark:active:border-gray-800!',
        },
        next: {
          iconContent: {
            html: chevronRightSvg,
          },
          className:
            'flex size-9! sm:size-10! p-0! items-center justify-center! rounded-lg! border! bg-transparent! border-gray-200! text-gray-700 hover:border-gray-200 hover:bg-gray-50! focus:shadow-none active:border-gray-200! active:bg-transparent! active:shadow-none! dark:border-gray-800! dark:text-gray-400 dark:hover:border-gray-800 dark:hover:bg-gray-900! dark:active:border-gray-800!',
        },
        addEventButton: {
          text: calendarEl.dataset.eventsUrl ? '+ Reservasi Baru' : 'Add Event +',
          click: () => {
            if (calendarEl.dataset.eventsUrl) {
              window.location.href = '/bookings/create';
            } else {
              handlers.openAddModal();
            }
          },
          className:
            'rounded-lg! border-0! bg-brand-500! px-3! sm:px-4! py-2! sm:py-2.5! text-xs! sm:text-sm! font-medium! text-white hover:bg-brand-600! focus:shadow-none! w-auto!',
        },
      },

      views: {
        multiMonthYear: {
          multiMonthMaxColumns: 3,
          singleMonthClass: 'fc-multimonth',
          tableClass:
            'overflow-visible! border-0! sm:border! sm:border-gray-200! dark:sm:border-gray-800! rounded-none! sm:rounded-lg! mt-0! bg-transparent!',
          singleMonthHeaderClass: 'mb-0! bg-white dark:bg-gray-900 sm:bg-transparent! ',
          tableHeaderClass: 'mb-0! rounded-none! sm:rounded-t-lg! bg-gray-50 dark:bg-gray-900!',
          tableBodyClass: 'mt-0!',
          singleMonthMinWidth: 280,
          showNonCurrentDates: true,
          singleMonthHeaderInnerClass: 'text-sm font-medium! text-gray-800 dark:text-white/90',
          dayHeaderRowClass: 'fc-multimonth-day-header-row',
          dayHeaderClass: (data) =>
            data.inPopover
              ? 'relative! border-b! border-gray-200! bg-gray-50/70! px-4! py-3! text-start! dark:border-gray-800! dark:bg-gray-800/50!'
              : 'border-0! bg-gray-50 py-2! dark:bg-gray-900 dark:sm:bg-transparent! first:rounded-none! first:sm:rounded-ss-lg! last:rounded-none! last:sm:rounded-se-lg!',
          dayHeaderInnerClass: (data) =>
            data.inPopover
              ? 'text-sm! font-semibold! text-gray-800! dark:text-white/90!'
              : 'py-1 text-[11px] sm:text-xs font-medium text-gray-400 uppercase',
          dayCellClass: (data) => {
            if (data.inPopover) return 'bg-transparent! p-3!'
            let cls = 'relative! p-0.5 sm:p-1!'
            if (data.isToday)
              cls +=
                ' isolate rounded-sm! bg-gray-100! dark:bg-gray-800/40! font-semibold text-brand-500 dark:text-brand-400'
            if (data.isOther) cls += ' bg-transparent!'
            return cls
          },
          dayCellInnerClass: (data) =>
            data.inPopover
              ? 'flex custom-scrollbar max-h-60 flex-col gap-1.5 overflow-y-auto'
              : 'h-0 max-h-0 overflow-hidden invisible',
          dayCellTopInnerClass: 'text-xs! sm:text-sm! text-gray-700 dark:text-gray-300',
          dayMaxEvents: 0,
          moreLinkClass: 'border-0! bg-transparent! p-0! hover:bg-transparent! focus:outline-none',
          rowMoreLinkClass:
            'absolute! -top-0.5! sm:-top-1! start-0.5! z-10! border-0! bg-transparent! p-0!',
          rowMoreLinkInnerClass: 'overflow-visible!',
          // Only show the bookmark when events are actually hidden in the row
          moreLinkContent(args) {
            return {
              html: args.num > 0 ? bookmarkSvg : '',
            }
          },
        },
        dayGridMonth: {
          dayMaxEvents: isMobile() ? 0 : 2,
          dayHeaderAlign: (data) => (data.inPopover ? 'start' : 'center'),
          dayHeaderClass: (data) =>
            data.inPopover
              ? 'relative! border-b! border-gray-200! bg-gray-50/70! px-4! py-3! text-start! dark:border-gray-800! dark:bg-gray-800/50!'
              : 'border-x-0! border-t border-gray-200! bg-gray-50 dark:border-gray-800! dark:bg-gray-900',
          dayHeaderInnerClass: (data) =>
            data.inPopover
              ? 'text-sm! font-semibold! text-gray-800! dark:text-white/90!'
              : 'px-1! py-2! sm:px-3! sm:py-3! md:px-5! md:py-4! text-xs! sm:text-sm! font-medium! text-gray-400 uppercase',
          dayCellClass: (data) => {
            if (data.inPopover) return 'bg-transparent! p-3!'
            return `bg-transparent! p-1! sm:p-2! ${
              data.isToday ? 'bg-gray-100! dark:bg-gray-800/40!' : ''
            }`
          },
          dayCellTopInnerClass: 'text-xs! sm:text-sm! text-gray-700 dark:text-gray-300',
          dayCellInnerClass: (data) => {
            if (data.inPopover)
              return 'flex custom-scrollbar max-h-60 flex-col gap-1.5 overflow-y-auto'
            if (isMobile()) return 'h-0 max-h-0 overflow-hidden invisible'
            return `${data.isToday ? 'rounded-sm!' : ''} -mx-1! sm:-mx-2!`
          },
          rowMoreLinkClass: isMobile()
            ? 'absolute! -top-1! -start-0.5! z-10! border-0! bg-transparent! p-0!'
            : '',
          rowMoreLinkInnerClass: isMobile() ? 'overflow-visible!' : '',
          moreLinkClass: 'border-0! bg-transparent! p-0! hover:bg-transparent! focus:outline-none',
          moreLinkContent(args) {
            if (isMobile()) {
              return {
                html: bookmarkSvg,
              }
            }
            return {
              html: `<span class="fc-more-link-badge inline-flex items-center rounded-sm bg-brand-50 px-1 py-0.5 text-[10px] font-medium text-brand-600 transition-colors hover:bg-brand-100 sm:px-1.5 sm:text-xs dark:bg-brand-500/15 dark:text-brand-400 dark:hover:bg-brand-500/25">+${args.num} more</span>`,
            }
          },
        },
        timeGridWeek: {
          slotDuration: '01:00:00',
          slotMinHeight: 56,
          allDaySlot: true,
          dayMaxEvents: isMobile() ? 0 : undefined,
          moreLinkClass: 'border-0! bg-transparent! p-0! hover:bg-transparent! focus:outline-none',
          rowMoreLinkClass: isMobile()
            ? 'absolute! -top-1! -start-0.5! z-10! border-0! bg-transparent! p-0!'
            : '',
          rowMoreLinkInnerClass: isMobile() ? 'overflow-visible!' : '',
          moreLinkContent: isMobile()
            ? () => ({
                html: bookmarkSvg,
              })
            : undefined,
          dayHeaderContent: (arg) => {
            const weekday = new Intl.DateTimeFormat(locale || 'en', {
              weekday: 'short',
            })
              .format(arg.date)
              .toUpperCase()
            const day = new Intl.DateTimeFormat(locale || 'en', {
              day: 'numeric',
            }).format(arg.date)
            return `${weekday} - ${day}`
          },
          dayHeaderClass: (data) =>
            `border-0! bg-gray-50! dark:bg-gray-900! ${
              data.isToday ? 'bg-gray-100/70! dark:bg-gray-800/60!' : ''
            }`,
          dayHeaderInnerClass: (data) =>
            `px-1.5! sm:px-3! py-2.5! sm:py-3.5! text-center! text-[11px]! sm:text-xs! font-medium! text-gray-500! uppercase! dark:text-gray-400! ${
              data.isToday ? 'font-semibold! text-brand-500! dark:text-brand-400!' : ''
            }`,
          slotHeaderDividerClass:
            'border-e! border-s-0! border-y-0! border-gray-200! dark:border-gray-800!',
          slotHeaderClass:
            'px-1.5! sm:px-3! py-1.5! sm:py-2! text-start! text-[11px]! sm:text-xs! font-medium! text-gray-400! dark:text-gray-500!',
          slotLaneClass: 'border-gray-100! dark:border-gray-800/60!',
          dayLaneClass: (data) =>
            `border-gray-200! dark:border-gray-800! ${
              data.isToday ? 'bg-brand-50/15! dark:bg-brand-500/[0.03]!' : ''
            }`,
          allDayDividerClass:
            'border-b! border-t-0! border-x-0! border-gray-200! p-0! bg-transparent! dark:border-gray-800!',
          allDayHeaderClass:
            'border-0! bg-gray-50! text-[11px]! sm:text-xs! font-medium! text-gray-500! dark:border-0! dark:bg-gray-900! dark:text-gray-400!',
        },
        timeGridDay: {
          slotDuration: '00:30:00',
          slotMinHeight: 48,
          allDaySlot: true,
          dayMaxEvents: isMobile() ? 0 : undefined,
          moreLinkClass: 'border-0! bg-transparent! p-0! hover:bg-transparent! focus:outline-none',
          rowMoreLinkClass: isMobile()
            ? 'absolute! -top-1! -start-0.5! z-10! border-0! bg-transparent! p-0!'
            : '',
          rowMoreLinkInnerClass: isMobile() ? 'overflow-visible!' : '',
          moreLinkContent: isMobile()
            ? () => ({
                html: bookmarkSvg,
              })
            : undefined,
          dayHeaderContent: (arg) => {
            const weekday = new Intl.DateTimeFormat(locale || 'en', {
              weekday: 'short',
            })
              .format(arg.date)
              .toUpperCase()
            const day = new Intl.DateTimeFormat(locale || 'en', {
              day: 'numeric',
            }).format(arg.date)
            return `${weekday} - ${day}`
          },
          dayHeaderClass: (data) =>
            `border-0! bg-gray-50! dark:bg-gray-900! ${
              data.isToday ? 'bg-gray-100/70! dark:bg-gray-800/60!' : ''
            }`,
          dayHeaderInnerClass: (data) =>
            `px-2! sm:px-4! py-2.5! sm:py-3.5! text-center! text-xs! font-medium! text-gray-500! uppercase! dark:text-gray-400! ${
              data.isToday ? 'font-semibold! text-brand-500! dark:text-brand-400!' : ''
            }`,
          slotHeaderDividerClass:
            'border-e! border-s-0! border-y-0! border-gray-200! dark:border-gray-800!',
          slotHeaderClass:
            'px-2! sm:px-3! py-1.5! sm:py-2! text-start! text-[11px]! sm:text-xs! font-medium! text-gray-400! dark:text-gray-500!',
          slotLaneClass: 'border-gray-100! dark:border-gray-800/60!',
          dayLaneClass: (data) =>
            `border-gray-200! dark:border-gray-800! ${
              data.isToday ? 'bg-brand-50/15! dark:bg-brand-500/[0.03]!' : ''
            }`,
          allDayDividerClass:
            'border-b! border-t-0! border-x-0! border-gray-200! p-0! bg-transparent! dark:border-gray-800!',
          allDayHeaderClass:
            'border-0! bg-gray-50! text-xs! font-medium! text-gray-500! dark:border-0! dark:bg-gray-900! dark:text-gray-400!',
        },
      },

      viewClass:
        'border-t! border-b-0! border-x-0! border-gray-200! bg-transparent! dark:border-gray-800! dark:bg-transparent!',
      tableHeaderClass: 'border-0! bg-gray-50! dark:bg-gray-900!',
      dayHeaderDividerClass:
        'border-b! border-t-0! border-x-0! border-gray-200! p-0! bg-transparent! dark:border-gray-800!',
      slotMinHeight: 56,
      slotHeaderDividerClass:
        'border-e! border-s-0! border-y-0! border-gray-200! dark:border-gray-800!',
      allDayDividerClass:
        'border-b! border-t-0! border-x-0! border-gray-200! p-0! bg-transparent! dark:border-gray-800!',
      eventClass: 'focus:shadow-none',
      nowIndicator: false,
      columnEventClass:
        'bg-transparent! border-0! p-1! shadow-none! hover:shadow-none! focus:outline-none',
      columnEventInnerClass: 'p-0! border-0! bg-transparent! h-full',
      tableHeaderSticky: true,
      tableClass: 'overflow-hidden bg-transparent!',
      rowEventClass:
        'bg-transparent! border-0! px-1! py-0.5! shadow-none! hover:shadow-none! focus:outline-none',
      rowEventInnerClass: 'p-0! border-0! bg-transparent!',
      popoverFormat: { month: 'short', day: 'numeric', year: 'numeric' },
      popoverClass:
        'z-99999! w-72 max-w-[calc(100vw-32px)] overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-lg dark:border-gray-800 dark:bg-gray-900',
      popoverCloseClass:
        'absolute end-3 top-2.5 flex size-7 cursor-pointer items-center justify-center rounded-lg text-gray-400 transition-colors hover:bg-gray-100 hover:text-gray-600 focus:outline-none dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-white',
      popoverCloseContent: {
        html: closeSvg,
      },

      selectable: true,
      events: calendarEl.dataset.eventsUrl || INITIAL_EVENTS,
      select: handlers.dateSelect,
      eventClick: handlers.eventClick,

      datesSet(arg) {
        handlers.datesSet(arg)
      },
      eventContent: (eventInfo) => ({ html: renderEventItem(eventInfo) }),
    };

    calendar = new Calendar(calendarEl, calendarOptions);
    calendar.render();

    window.addEventListener('calendar-save', (e) => {
        const formData = e.detail;
        const titleVal = (formData.title || '').trim() || (selectedEvent ? 'Event' : 'New Event');
        const startDateVal = formData.start;
        const endDateVal = formData.end || startDateVal;
        const levelVal = formData.level || 'Primary';

        if (selectedEvent) {
            selectedEvent.setProp('title', titleVal);
            selectedEvent.setStart(startDateVal);
            selectedEvent.setEnd(endDateVal || startDateVal);
            selectedEvent.setExtendedProp('calendar', levelVal);
        } else {
            calendar.addEvent({
                id: Date.now().toString(),
                title: titleVal,
                start: startDateVal,
                end: endDateVal || startDateVal,
                allDay: true,
                extendedProps: { calendar: levelVal },
            });
        }
        selectedEvent = null;
    });

    window.addEventListener('resize', () => {
        const mobile = window.innerWidth < 640;
        if (isMobileState !== mobile) {
            isMobileState = mobile;
            calendar.setOption('views', {
                dayGridMonth: { dayMaxEvents: mobile ? 0 : 2 },
                timeGridWeek: { dayMaxEvents: mobile ? 0 : undefined },
                timeGridDay: { dayMaxEvents: mobile ? 0 : undefined },
            });
        }
    });
}
