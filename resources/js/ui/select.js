import { anchorTo, onEscape, onOutsidePress } from './floating';

let uid = 0;
const ITEM = '[data-slot="select-item"]:not([data-disabled])';

/** Select behaviour for <x-ui.select>: click / Enter / ArrowDown opens, arrow keys move, Enter picks, Escape closes. */
export default (config = {}) => ({
    open: false,
    id: `ui-select-${++uid}`,
    multiple: config.multiple ?? false,
    value: config.value ?? (config.multiple ? [] : null),
    options: {},
    cleanup: [],

    get state() {
        return this.open ? 'open' : 'closed';
    },

    get label() {
        if (this.multiple) return this.value.length ? this.value.map((v) => this.options[v] ?? this.options[String(v)]).join(', ') : null;
        return this.value != null ? (this.options[this.value] ?? this.options[String(this.value)]) : null;
    },

    isSelected(value) {
        if (this.value == null) return false;
        return this.multiple 
            ? this.value.map(String).includes(String(value)) 
            : String(this.value) === String(value);
    },

    register(value, label) {
        this.options[value] = label;
    },

    setValue(value) {
        this.value = value;
    },

    toggle() {
        this.open ? this.close() : this.show();
    },

    show() {
        this.open = true;
        this.$nextTick(() => this.mount());
    },

    close(restoreFocus = true) {
        if (!this.open) return;
        this.open = false;
        this.unmount();
        if (restoreFocus) this.$refs.trigger?.focus();
    },

    pick(value) {
        if (this.multiple) {
            this.value = this.value.includes(value) ? this.value.filter((v) => v !== value) : [...this.value, value];
            this.$refs.trigger?.dispatchEvent(new CustomEvent('change', { bubbles: true, detail: { value: this.value } }));
            this.$dispatch('change', { value: this.value });
            return;
        }
        this.value = value;
        this.$refs.trigger?.dispatchEvent(new CustomEvent('change', { bubbles: true, detail: { value } }));
        this.$dispatch('change', { value });
        this.close();
    },

    items() {
        return [...(this.$refs.content?.querySelectorAll(ITEM) ?? [])];
    },

    highlight(item) {
        this.items().forEach((el) => el.removeAttribute('data-highlighted'));
        if (item) {
            item.setAttribute('data-highlighted', '');
            item.focus({ preventScroll: true });
        }
    },

    mount() {
        const { trigger, wrapper, content } = this.$refs;
        if (!trigger || !wrapper) return;
        const options = JSON.parse(content.dataset.options || '{}');
        content.style.setProperty('--reka-select-trigger-width', `${trigger.offsetWidth}px`);
        this.cleanup.push(anchorTo({ anchor: trigger, wrapper, content, ...options }));
        this.cleanup.push(onOutsidePress([wrapper, trigger], () => this.close(false)));
        this.cleanup.push(onEscape(() => this.close()));
        requestAnimationFrame(() => this.highlight(this.items().find((el) => (this.multiple ? this.value.map(String).includes(el.dataset.value) : el.dataset.value === String(this.value))) ?? this.items()[0]));
    },

    unmount() {
        this.cleanup.forEach((fn) => fn());
        this.cleanup = [];
    },

    onTriggerKeydown(event) {
        if (['Enter', ' ', 'ArrowDown', 'ArrowUp'].includes(event.key)) {
            event.preventDefault();
            this.open ? null : this.show();
        }
    },

    onContentKeydown(event) {
        const items = this.items();
        const index = items.indexOf(document.activeElement);
        if (event.key === 'ArrowDown') this.highlight(items[(index + 1) % items.length]);
        else if (event.key === 'ArrowUp') this.highlight(items[(index - 1 + items.length) % items.length]);
        else if (event.key === 'Home') this.highlight(items[0]);
        else if (event.key === 'End') this.highlight(items.at(-1));
        else if (event.key === 'Enter' || event.key === ' ') items[index]?.click();
        else if (event.key === 'Tab') return this.close(false);
        else return;
        event.preventDefault();
    },
});
