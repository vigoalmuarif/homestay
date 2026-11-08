<?php

namespace App\Support;

/**
 * Translates keys whose strings use `{name}` placeholders and simple ICU plurals
 * (`{count, plural, one {# Variant} other {# Variants}}`).
 */
class I18n
{
    public static function t(string $key, array $params = []): string
    {
        $text = __($key);

        $text = preg_replace_callback('/\{(\w+),\s*plural,\s*((?:\w+\s*\{[^}]*\}\s*)+)\}/', function ($m) use ($params) {
            $count = (int) ($params[$m[1]] ?? 0);
            preg_match_all('/(\w+)\s*\{([^}]*)\}/', $m[2], $branches, PREG_SET_ORDER);
            $forms = [];
            foreach ($branches as $b) {
                $forms[$b[1]] = $b[2];
            }
            $mod100 = $count % 100;
            $category = match (true) {
                $count === 0 && isset($forms['zero']) => 'zero',
                $count === 1 => 'one',
                $count === 2 && isset($forms['two']) => 'two',
                $mod100 >= 3 && $mod100 <= 10 && isset($forms['few']) => 'few',
                $mod100 >= 11 && $mod100 <= 99 && isset($forms['many']) => 'many',
                default => 'other',
            };

            return str_replace('#', (string) $count, $forms[$category] ?? $forms['other'] ?? '');
        }, $text);

        foreach ($params as $name => $value) {
            $text = str_replace('{' . $name . '}', (string) $value, $text);
        }

        return $text;
    }
}
