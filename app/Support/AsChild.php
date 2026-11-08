<?php

namespace App\Support;

/**
 * Implements the `as-child` pattern used by the UI components: instead of rendering their
 * own wrapper element, the component merges its attributes and classes into the first element of the slot.
 */
class AsChild
{
    /**
     * @param  array<string, string|bool|null>  $attributes  attribute name => value (null/false skips, true renders a bare attribute); they replace attributes of the same name on the child
     */
    public static function apply(string $html, array $attributes = [], string $classes = ''): string
    {
        $html = ltrim($html);

        if (! preg_match('/^<([a-zA-Z][\w:-]*)((?:\s+[^\s=>\/]+(?:\s*=\s*(?:"[^"]*"|\'[^\']*\'|[^\s>]+))?)*)\s*(\/?)>/s', $html, $m)) {
            return $html;
        }

        [$whole, $tag, $existing, $selfClose] = $m;

        $existingClass = '';
        if (preg_match('/\sclass\s*=\s*(?:"([^"]*)"|\'([^\']*)\')/', $existing, $cm)) {
            $existingClass = $cm[1] !== '' ? $cm[1] : ($cm[2] ?? '');
            $existingClass = html_entity_decode($existingClass, ENT_QUOTES);
            $existing = str_replace($cm[0], '', $existing);
        }

        $out = '';
        foreach ($attributes as $name => $value) {
            if ($value === null || $value === false) {
                continue;
            }
            // The component's attributes replace the same attributes on the child.
            $existing = preg_replace('/\s' . preg_quote($name, '/') . '(?:\s*=\s*(?:"[^"]*"|\'[^\']*\'|[^\s>]+))?(?=\s|$)/', '', $existing);
            $out .= $value === true ? " {$name}" : ' ' . $name . '="' . e((string) $value) . '"';
        }

        $class = TwMerge::merge($classes, $existingClass);
        if ($class !== '') {
            $out .= ' class="' . e($class) . '"';
        }

        return '<' . $tag . $out . $existing . ($selfClose ? ' /' : '') . '>' . substr($html, strlen($whole));
    }
}
