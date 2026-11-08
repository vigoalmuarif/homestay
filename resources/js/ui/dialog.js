import { onEscape } from './floating';

let uid = 0;
const FOCUSABLE = 'a[href], button:not([disabled]), input:not([disabled]):not([type="hidden"]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])';

/**
 * Modal dialog behaviour for <x-ui.dialog>: focus moves into the dialog and is trapped there, Escape and a press on the
 * overlay close it, page scrolling is locked and focus returns to the trigger on close.
 */
export default () => ({
    open: false,
    id: `ui-dialog-${++uid}`,
    cleanup: [],
    previousFocus: null,

    get state() {
        return this.open ? 'open' : 'closed';
    },

    get titleId() {
        return `${this.id}-title`;
    },

    get descriptionId() {
        return `${this.id}-description`;
    },

    show() {
        this.previousFocus = document.activeElement;
        this.open = true;
        this.$nextTick(() => this.mount());
    },

    close() {
        if (!this.open) return;
        this.open = false;
        this.unmount();
        (this.previousFocus ?? this.$refs.trigger)?.focus?.();
    },

    mount() {
        const content = this.$refs.content;
        if (!content) return;

        // Lock page scroll without shifting the layout.
        const scrollbar = window.innerWidth - document.documentElement.clientWidth;
        const { overflow, paddingRight } = document.body.style;
        document.body.style.overflow = 'hidden';
        if (scrollbar > 0) document.body.style.paddingRight = `${scrollbar}px`;
        this.cleanup.push(() => Object.assign(document.body.style, { overflow, paddingRight }));

        this.cleanup.push(onEscape(() => this.close()));

        const trap = (event) => {
            if (event.key !== 'Tab') return;
            const items = [...content.querySelectorAll(FOCUSABLE)].filter((el) => el.offsetParent !== null);
            if (!items.length) {
                event.preventDefault();
                return;
            }
            const first = items[0];
            const last = items[items.length - 1];
            if (event.shiftKey && (document.activeElement === first || document.activeElement === content)) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        };
        content.addEventListener('keydown', trap);
        this.cleanup.push(() => content.removeEventListener('keydown', trap));

        // Focus the first field with the caret at the end (no highlighted text).
        requestAnimationFrame(() => {
            const target = content.querySelector('input:not([type="hidden"]), textarea, select') ?? content.querySelector(FOCUSABLE) ?? content;
            target.focus({ preventScroll: true });
            if ((target instanceof HTMLInputElement || target instanceof HTMLTextAreaElement)) {
                try { target.setSelectionRange(target.value.length, target.value.length); } catch (e) { /* unsupported input type */ }
            }
        });
    },

    unmount() {
        this.cleanup.forEach((fn) => fn());
        this.cleanup = [];
    },
});
