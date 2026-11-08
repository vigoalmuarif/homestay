import { arrow as arrowMiddleware, autoUpdate, computePosition, flip, offset, shift } from '@floating-ui/dom';

const OPPOSITE = { top: 'bottom', bottom: 'top', left: 'right', right: 'left' };

/**
 * Anchors a floating element to a trigger (tooltip / popover / dropdown content).
 *
 * `wrapper` is the fixed-position element that gets moved, `content` is the element that receives
 * `data-side` / `data-align` (the styling hooks). Returns a cleanup function.
 */
export function anchorTo({ anchor, wrapper, content, side = 'bottom', align = 'center', sideOffset = 8, alignOffset = 0, arrow = null, arrowPadding = 8 }) {
    const rtl = document.documentElement.dir === 'rtl';
    // `inline-start` / `inline-end` follow the writing direction.
    const resolved = side === 'inline-start' ? (rtl ? 'right' : 'left') : side === 'inline-end' ? (rtl ? 'left' : 'right') : side;
    const placement = align === 'center' ? resolved : `${resolved}-${align}`;

    Object.assign(wrapper.style, { position: 'fixed', left: '0px', top: '0px', minWidth: 'max-content', zIndex: getComputedStyle(content).zIndex });
    wrapper.__anchor = anchor;

    // The arrow height is added on top of `sideOffset`; floating-ui does not, so do it here.
    const arrowHeight = arrow ? Number(arrow.querySelector('svg')?.getAttribute('height') ?? 6) : 0;

    const update = async () => {
        const middleware = [
            offset({ mainAxis: sideOffset + arrowHeight, alignmentAxis: alignOffset }),
            flip({ padding: 8 }),
            shift({ padding: 8 }),
        ];
        if (arrow) middleware.push(arrowMiddleware({ element: arrow, padding: arrowPadding }));

        const { x, y, placement: actual, middlewareData } = await computePosition(anchor, wrapper, { strategy: 'fixed', placement, middleware });
        const [actualSide, actualAlign = 'center'] = actual.split('-');

        wrapper.style.transform = `translate(${Math.round(x)}px, ${Math.round(y)}px)`;
        content.dataset.side = side.startsWith('inline') ? side : actualSide;
        content.dataset.align = actualAlign;

        if (arrow && middlewareData.arrow) {
            const { x: ax, y: ay } = middlewareData.arrow;
            const opposite = OPPOSITE[actualSide];
            const horizontal = actualSide === 'top' || actualSide === 'bottom';
            Object.assign(arrow.style, {
                position: 'absolute',
                left: horizontal && ax != null ? `${ax}px` : '',
                top: !horizontal && ay != null ? `${ay}px` : '',
                right: '', bottom: '',
                [opposite]: '0px',
                transformOrigin: { top: '', right: '0 0', bottom: 'center 0', left: '100% 0' }[actualSide],
                transform: { top: 'translateY(100%)', right: 'translateY(50%) rotate(90deg) translateX(-50%)', bottom: 'rotate(180deg)', left: 'translateY(50%) rotate(-90deg) translateX(50%)' }[actualSide],
            });
        }
    };

    const cleanup = autoUpdate(anchor, wrapper, update);
    return () => {
        cleanup();
        delete wrapper.__anchor;
    };
}

/** Calls `handler` when a pointer press lands outside every given element. Returns a cleanup function. */
export function onOutsidePress(elements, handler) {
    const listener = (event) => {
        const list = typeof elements === 'function' ? elements() : elements;
        if (list.some((el) => el && el.contains(event.target))) return;

        let curr = event.target;
        while (curr && curr !== document.body && curr !== document.documentElement) {
            if (curr.__anchor) {
                curr = curr.__anchor;
                if (list.some((el) => el && el.contains(curr))) return;
                continue;
            }
            curr = curr.parentElement;
        }

        handler(event);
    };
    document.addEventListener('pointerdown', listener, true);

    return () => document.removeEventListener('pointerdown', listener, true);
}

/** Calls `handler` when Escape is pressed. Returns a cleanup function. */
export function onEscape(handler) {
    const listener = (event) => {
        if (event.key === 'Escape') handler(event);
    };
    document.addEventListener('keydown', listener);

    return () => document.removeEventListener('keydown', listener);
}
