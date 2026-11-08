<?php

namespace App\Support;

/**
 * Lightweight Tailwind class merger used by the UI Blade components.
 *
 * It mirrors the behaviour of `tailwind-merge` for the utility groups the UI kit relies on:
 * when two classes of the same group (and the same variant prefix) are present, the later one
 * wins. Classes it does not recognise are only de-duplicated.
 */
class TwMerge
{
    /** Group => groups it overrides when it appears later in the list. */
    private const CONFLICTS = [
        'p' => ['px', 'py', 'pt', 'pr', 'pb', 'pl', 'ps', 'pe'],
        'px' => ['pr', 'pl', 'ps', 'pe'],
        'py' => ['pt', 'pb'],
        'm' => ['mx', 'my', 'mt', 'mr', 'mb', 'ml', 'ms', 'me'],
        'mx' => ['mr', 'ml', 'ms', 'me'],
        'my' => ['mt', 'mb'],
        'size' => ['w', 'h'],
        'gap' => ['gap-x', 'gap-y'],
        'rounded' => ['rounded-s', 'rounded-e', 'rounded-t', 'rounded-r', 'rounded-b', 'rounded-l', 'rounded-ss', 'rounded-se', 'rounded-ee', 'rounded-es', 'rounded-tl', 'rounded-tr', 'rounded-br', 'rounded-bl'],
        'rounded-s' => ['rounded-ss', 'rounded-es'],
        'rounded-e' => ['rounded-se', 'rounded-ee'],
        'rounded-t' => ['rounded-tl', 'rounded-tr'],
        'rounded-r' => ['rounded-tr', 'rounded-br'],
        'rounded-b' => ['rounded-br', 'rounded-bl'],
        'rounded-l' => ['rounded-tl', 'rounded-bl'],
        'border-w' => ['border-w-x', 'border-w-y', 'border-w-t', 'border-w-r', 'border-w-b', 'border-w-l', 'border-w-s', 'border-w-e'],
        'border-w-x' => ['border-w-r', 'border-w-l'],
        'border-w-y' => ['border-w-t', 'border-w-b'],
        'inset' => ['inset-x', 'inset-y', 'start', 'end', 'top', 'right', 'bottom', 'left'],
        'inset-x' => ['right', 'left'],
        'inset-y' => ['top', 'bottom'],
        'overflow' => ['overflow-x', 'overflow-y'],
        'font-size' => ['leading'],
    ];

    /**
     * @param  array<int, string|array|null>  $classes
     */
    public static function merge(...$classes): string
    {
        $tokens = [];
        foreach ($classes as $class) {
            if (is_string($class)) {
                // Classes passed as bound component attributes (`:class="..."`) arrive HTML-escaped; undo it so `&` and `'` survive.
                $class = html_entity_decode($class, ENT_QUOTES);
            }
            if (is_array($class)) {
                $class = implode(' ', array_filter($class));
            }
            if ($class === null || $class === '' || $class === false) {
                continue;
            }
            foreach (preg_split('/\s+/', trim((string) $class)) as $token) {
                if ($token !== '') {
                    $tokens[] = $token;
                }
            }
        }

        // Walk right to left so the last class of a group wins.
        $seen = [];
        $kept = [];
        for ($i = count($tokens) - 1; $i >= 0; $i--) {
            [$variants, $important, $base] = self::parse($tokens[$i]);
            $group = self::group($base);
            $key = $variants . '|' . ($important ? '!' : '') . '|' . ($group ?? 'raw:' . $base);

            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;

            if ($group !== null && isset(self::CONFLICTS[$group])) {
                foreach (self::CONFLICTS[$group] as $overridden) {
                    $seen[$variants . '|' . ($important ? '!' : '') . '|' . $overridden] = true;
                }
            }

            $kept[] = $tokens[$i];
        }

        return implode(' ', array_reverse($kept));
    }

    /** @return array{0: string, 1: bool, 2: string} variants, important, base utility */
    private static function parse(string $token): array
    {
        $depth = 0;
        $last = -1;
        $length = strlen($token);
        for ($i = 0; $i < $length; $i++) {
            $char = $token[$i];
            if ($char === '[' || $char === '(') {
                $depth++;
            } elseif ($char === ']' || $char === ')') {
                $depth--;
            } elseif ($char === ':' && $depth === 0) {
                $last = $i;
            }
        }

        $variants = $last >= 0 ? substr($token, 0, $last) : '';
        $base = $last >= 0 ? substr($token, $last + 1) : $token;

        $important = false;
        if (str_starts_with($base, '!')) {
            $important = true;
            $base = substr($base, 1);
        } elseif (str_ends_with($base, '!')) {
            $important = true;
            $base = substr($base, 0, -1);
        }

        return [$variants, $important, $base];
    }

    private static function group(string $base): ?string
    {
        $b = ltrim($base, '-');
        $color = '(?:inherit|current|transparent|black|white|brand|gray|blue-light|orange|success|error|warning|slate|zinc|neutral|stone|red|amber|yellow|lime|green|emerald|teal|cyan|sky|blue|indigo|violet|purple|fuchsia|pink|rose|\[#|\[rgb|\[var|\(--)';

        $rules = [
            '/^(block|inline-block|inline|flex|inline-flex|grid|inline-grid|contents|hidden|table|flow-root)$/' => 'display',
            '/^(static|fixed|absolute|relative|sticky)$/' => 'position',
            '/^p-/' => 'p', '/^px-/' => 'px', '/^py-/' => 'py', '/^pt-/' => 'pt', '/^pr-/' => 'pr', '/^pb-/' => 'pb', '/^pl-/' => 'pl', '/^ps-/' => 'ps', '/^pe-/' => 'pe',
            '/^m-/' => 'm', '/^mx-/' => 'mx', '/^my-/' => 'my', '/^mt-/' => 'mt', '/^mr-/' => 'mr', '/^mb-/' => 'mb', '/^ml-/' => 'ml', '/^ms-/' => 'ms', '/^me-/' => 'me',
            '/^size-/' => 'size', '/^w-/' => 'w', '/^h-/' => 'h',
            '/^min-w-/' => 'min-w', '/^max-w-/' => 'max-w', '/^min-h-/' => 'min-h', '/^max-h-/' => 'max-h',
            '/^gap-x-/' => 'gap-x', '/^gap-y-/' => 'gap-y', '/^gap-/' => 'gap',
            '/^space-x-/' => 'space-x', '/^space-y-/' => 'space-y',
            '/^rounded-(ss|se|ee|es|tl|tr|br|bl)(-|$)/' => null,
            '/^rounded-s(-|$)/' => 'rounded-s', '/^rounded-e(-|$)/' => 'rounded-e', '/^rounded-t(-|$)/' => 'rounded-t',
            '/^rounded-r(-|$)/' => 'rounded-r', '/^rounded-b(-|$)/' => 'rounded-b', '/^rounded-l(-|$)/' => 'rounded-l',
            '/^rounded(-|$)/' => 'rounded',
            '/^text-(start|end|left|right|center|justify)$/' => 'text-align',
            '/^text-(balance|pretty|wrap|nowrap)$/' => 'text-wrap',
            // Custom sizes registered with tailwind-merge. Other custom `text-*` classes
            // (e.g. `text-theme-xl`) are not known sizes, so they are treated as colours and conflict with colour classes.
            '/^text-(xs|sm|base|lg|\d?xl|heading-(?:one|two|three|four|five|six)|title-(?:sm|lg)|\[\d+(px|rem)\])$/' => 'font-size',
            '/^text-(ellipsis|clip)$/' => 'text-overflow',
            '/^text-/' => 'text-color',
            '/^font-(thin|extralight|light|normal|medium|semibold|bold|extrabold|black)$/' => 'font-weight',
            '/^font-(outfit|sans|serif|mono)$/' => 'font-family',
            '/^leading-/' => 'leading',
            '/^bg-(clip|origin|linear|gradient|none|no-repeat|repeat|cover|contain|center|top|bottom|left|right|fixed|local|scroll|blend)/' => null,
            '/^bg-/' => 'bg-color',
            '/^border(-\d+)?$/' => 'border-w',
            '/^border-t(-\d+)?$/' => 'border-w-t', '/^border-r(-\d+)?$/' => 'border-w-r', '/^border-b(-\d+)?$/' => 'border-w-b', '/^border-l(-\d+)?$/' => 'border-w-l', '/^border-s(-\d+)?$/' => 'border-w-s', '/^border-e(-\d+)?$/' => 'border-w-e',
            '/^border-x(-\d+)?$/' => 'border-w-x', '/^border-y(-\d+)?$/' => 'border-w-y',
            '/^border-(solid|dashed|dotted|double|none|hidden)$/' => 'border-style',
            '/^border-' . $color . '/' => 'border-color',
            '/^shadow(-|$)/' => 'shadow',
            '/^ring(-\d+)?$/' => 'ring-w',
            '/^ring-' . $color . '/' => 'ring-color',
            '/^opacity-/' => 'opacity',
            '/^z-/' => 'z',
            '/^(flex-row|flex-col|flex-row-reverse|flex-col-reverse)$/' => 'flex-direction',
            '/^(flex-wrap|flex-nowrap|flex-wrap-reverse)$/' => 'flex-wrap',
            '/^items-/' => 'items', '/^justify-items-/' => 'justify-items', '/^justify-self-/' => 'justify-self', '/^justify-/' => 'justify',
            '/^self-/' => 'self', '/^content-/' => 'align-content',
            '/^whitespace-/' => 'whitespace',
            '/^overflow-x-/' => 'overflow-x', '/^overflow-y-/' => 'overflow-y', '/^overflow-/' => 'overflow',
            '/^cursor-/' => 'cursor', '/^select-/' => 'select', '/^pointer-events-/' => 'pointer-events',
            '/^(top|right|bottom|left|start|end)-/' => '__edge',
            '/^inset-x-/' => 'inset-x', '/^inset-y-/' => 'inset-y', '/^inset-/' => 'inset',
            '/^underline-offset-/' => 'underline-offset',
            '/^(underline|no-underline|line-through|overline)$/' => 'text-decoration',
            '/^grid-cols-/' => 'grid-cols', '/^col-start-/' => 'col-start', '/^row-start-/' => 'row-start', '/^col-span-/' => 'col-span', '/^row-span-/' => 'row-span',
            '/^transition(-|$)/' => 'transition',
            '/^duration-/' => 'duration', '/^ease-/' => 'ease',
            '/^translate-x-/' => 'translate-x', '/^translate-y-/' => 'translate-y',
            '/^(truncate|text-ellipsis|text-clip)$/' => 'text-overflow',
            '/^object-/' => 'object',
            '/^aspect-/' => 'aspect',
            '/^tabular-nums$/' => 'numeric',
            '/^(shrink|grow)(-|$)/' => null,
            '/^\[([a-z-]+):/' => '__arbitrary',
            '/^from-/' => 'gradient-from', '/^to-/' => 'gradient-to', '/^via-/' => 'gradient-via',
        ];

        foreach ($rules as $pattern => $group) {
            if (preg_match($pattern, $b)) {
                if ($group === '__edge') {
                    return preg_replace('/-.*/', '', $b);
                }
                if ($group === '__arbitrary') {
                    preg_match('/^\[([a-z-]+):/', $b, $m);

                    return 'arbitrary-' . $m[1];
                }

                return $group;
            }
        }

        return null;
    }
}
