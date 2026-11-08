import { anchorTo, onEscape, onOutsidePress } from './floating';

let uid = 0;

/** Closes every sub menu that belongs directly to `content` (nested sub menus close through their own close()). */
function closeSubMenus(content, except = null) {
    content?.querySelectorAll('[data-slot="dropdown-menu-sub"]').forEach((el) => {
        if (el !== except && el.closest('[role="menu"]') === content) window.Alpine.$data(el).close(false);
    });
}
const ITEM = '[role="menuitem"], [role="menuitemcheckbox"], [role="menuitemradio"]';

/**
 * Menu behaviour shared by <x-ui.dropdown-menu> and its sub menus: arrow keys / Home / End move
 * the highlighted item, Enter / Space select it, Escape closes, a press outside closes, focus returns to the trigger.
 * Sub menus open on hover or ArrowRight (ArrowLeft in RTL) and close on ArrowLeft / Escape.
 */
export default (config = {}) => ({
    open: false,
    id: `ui-menu-${++uid}`,
    cleanup: [],
    isSub: config.sub ?? false,

    init() {
        if (config.defaultOpen) this.$nextTick(() => requestAnimationFrame(() => this.show()));
    },

    get state() {
        return this.open ? 'open' : 'closed';
    },

    toggle() {
        this.open ? this.close() : this.show();
    },

    show(focusFirst = false) {
        if (this.isSub) closeSubMenus(this.$root.parentElement.closest('[role="menu"]'), this.$root);
        this.open = true;
        this.$nextTick(() => this.mount(focusFirst));
    },

    close(restoreFocus = true) {
        if (!this.open) return;
        closeSubMenus(this.$refs.content);
        this.open = false;
        this.unmount();
        if (restoreFocus) this.$refs.trigger?.focus();
    },

    /** Closes this menu and every ancestor menu (used after an item is selected). */
    closeAll() {
        setTimeout(() => {
            this.close(false);
            this.$dispatch('ui-menu-close');
        }, 0);
    },

    items() {
        return [...(this.$refs.content?.querySelectorAll(ITEM) ?? [])].filter((el) => !el.hasAttribute('data-disabled') && el.closest('[role="menu"]') === this.$refs.content);
    },

    highlight(item) {
        if (!item?.matches('[data-slot="dropdown-menu-sub-trigger"]')) closeSubMenus(this.$refs.content);
        // A sub trigger runs in the sub menu's scope, so clear the highlight in the menu the item lives in.
        const menu = item?.closest('[role="menu"]') ?? this.$refs.content;
        menu?.querySelectorAll(ITEM).forEach((el) => el.removeAttribute('data-highlighted'));
        if (item) {
            item.setAttribute('data-highlighted', '');
            item.focus({ preventScroll: true });
        } else {
            this.$refs.content?.focus({ preventScroll: true });
        }
    },

    mount(focusFirst) {
        const { trigger, wrapper, content } = this.$refs;
        if (!trigger || !wrapper) return;
        const options = JSON.parse(content.dataset.options || '{}');
        this.cleanup.push(anchorTo({ anchor: trigger, wrapper, content, ...options }));
        if (!this.isSub) {
            this.cleanup.push(onOutsidePress([wrapper, trigger], () => this.close(false)));
            this.cleanup.push(onEscape(() => this.close()));
        }
        const closeAll = () => this.close(false);
        window.addEventListener('ui-menu-close', closeAll);
        this.cleanup.push(() => window.removeEventListener('ui-menu-close', closeAll));
        requestAnimationFrame(() => (focusFirst ? this.highlight(this.items()[0]) : content.focus({ preventScroll: true })));
    },

    unmount() {
        this.cleanup.forEach((fn) => fn());
        this.cleanup = [];
    },

    onTriggerKeydown(event) {
        if (['Enter', ' ', 'ArrowDown'].includes(event.key)) {
            event.preventDefault();
            this.open ? this.highlight(this.items()[0]) : this.show(true);
        } else if (event.key === 'ArrowUp') {
            event.preventDefault();
            this.show(false);
            this.$nextTick(() => requestAnimationFrame(() => this.highlight(this.items().at(-1))));
        }
    },

    onSubTriggerKeydown(event) {
        const forward = document.documentElement.dir === 'rtl' ? 'ArrowLeft' : 'ArrowRight';
        if (event.key === forward || event.key === 'Enter' || event.key === ' ') {
            event.preventDefault();
            event.stopPropagation();
            this.show(true);
        }
    },

    onContentKeydown(event) {
        const items = this.items();
        const index = items.indexOf(document.activeElement);
        const back = document.documentElement.dir === 'rtl' ? 'ArrowRight' : 'ArrowLeft';

        if (event.key === 'ArrowDown') this.highlight(items[(index + 1) % items.length]);
        else if (event.key === 'ArrowUp') this.highlight(items[(index - 1 + items.length) % items.length]);
        else if (event.key === 'Home') this.highlight(items[0]);
        else if (event.key === 'End') this.highlight(items.at(-1));
        else if (event.key === 'Tab') return this.close(false);
        else if (this.isSub && (event.key === back || event.key === 'Escape')) {
            event.stopPropagation();
            this.close();
        } else return;

        event.preventDefault();
    },
});
