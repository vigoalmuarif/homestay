import { anchorTo, onEscape, onOutsidePress } from './floating';

let uid = 0;
const ITEM = '[data-slot="combobox-item"]:not([data-disabled])';

/** Combobox behaviour for <x-ui.combobox>: typing filters the items, arrows move, Enter picks, Escape closes. Dispatches `change` with { value }. */
export default (config = {}) => ({
    open: false,
    id: `ui-combobox-${++uid}`,
    value: config.value ?? null,
    query: '',
    filtering: false,
    options: {},
    cleanup: [],

    get state() {
        return this.open ? 'open' : 'closed';
    },

    get label() {
        return this.value != null ? (this.options[this.value] ?? this.options[String(this.value)]) : null;
    },

    register(value, label) {
        this.options[value] = label;
        if (this.value != null && String(this.value) === String(value) && !this.query) this.query = label;
    },

    matches(label) {
        return !this.filtering || !this.query.trim() || label.toLowerCase().includes(this.query.trim().toLowerCase());
    },

    get visibleCount() {
        // Track reactive dependencies so Alpine re-evaluates when query, state, or filter changes
        const _q = this.query;
        const _f = this.filtering;
        const _o = this.open;
        if (window.Alpine?.store('booking')) {
            const _rt = Alpine.store('booking').roomTypeId;
            const _ar = Alpine.store('booking').availableRooms;
            const _ld = Alpine.store('booking').isRoomsLoaded;
        }

        if (this.$refs.content) {
            return this.items().length;
        }
        return Object.values(this.options).filter((label) => this.matches(label)).length;
    },

    onInput() {
        this.filtering = true;
        if (!this.open) this.show();
        this.$nextTick(() => this.highlight(this.items()[0]));
    },

    toggle() {
        this.open ? this.close() : this.show();
        this.$refs.input?.focus();
    },

    show() {
        this.open = true;
        this.$nextTick(() => this.mount());
    },

    close() {
        if (!this.open) return;
        this.open = false;
        this.filtering = false;
        this.query = this.label ?? '';
        this.unmount();
    },

    pick(value) {
        this.value = value;
        this.query = value != null ? (this.options[value] ?? this.options[String(value)] ?? '') : '';
        this.$refs.anchor?.dispatchEvent(new CustomEvent('change', { bubbles: true, detail: { value } }));
        this.$dispatch('change', { value });
        this.close();
        this.$refs.input?.focus();
    },

    setValue(value) {
        this.value = value;
        this.query = value != null ? (this.options[value] ?? this.options[String(value)] ?? '') : '';
    },

    clear() {
        this.value = null;
        this.query = '';
        this.filtering = false;
        this.$refs.anchor?.dispatchEvent(new CustomEvent('change', { bubbles: true, detail: { value: null } }));
        this.$dispatch('change', { value: null });
        this.$refs.input?.focus();
    },

    items() {
        return [...(this.$refs.content?.querySelectorAll(ITEM) ?? [])].filter(
            (el) => el.style.display !== 'none'
        );
    },

    highlight(item) {
        this.$refs.content?.querySelectorAll(ITEM).forEach((el) => el.removeAttribute('data-highlighted'));
        item?.setAttribute('data-highlighted', '');
        item?.scrollIntoView({ block: 'nearest' });
    },

    mount() {
        const { anchor, wrapper, content } = this.$refs;
        if (!anchor || !wrapper) return;
        const options = JSON.parse(content.dataset.options || '{}');
        content.style.setProperty('--reka-combobox-trigger-width', `${anchor.offsetWidth}px`);
        this.cleanup.push(anchorTo({ anchor, wrapper, content, ...options }));
        this.cleanup.push(onOutsidePress([wrapper, anchor], () => this.close()));
        this.cleanup.push(onEscape(() => this.close()));
        requestAnimationFrame(() => this.highlight(this.items().find((el) => el.dataset.value === String(this.value)) ?? this.items()[0]));
    },

    unmount() {
        this.cleanup.forEach((fn) => fn());
        this.cleanup = [];
    },

    onKeydown(event) {
        const items = this.items();
        const current = items.findIndex((el) => el.hasAttribute('data-highlighted'));
        if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
            event.preventDefault();
            if (!this.open) return this.show();
            const step = event.key === 'ArrowDown' ? 1 : -1;
            this.highlight(items[(current + step + items.length) % items.length]);
        } else if (event.key === 'Enter' && this.open) {
            event.preventDefault();
            items[current]?.click();
        } else if (event.key === 'Tab') {
            this.close();
        }
    },
});
