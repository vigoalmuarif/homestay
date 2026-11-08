@props([
    'id' => null,
    'name' => null,
    'placeholder' => '-- Pilih Opsi --',
    'ajaxUrl' => null,
    'dropdownParent' => null,
    'allowClear' => true,
    'required' => false,
    'value' => null,
    'valueLabel' => null,
    'options' => [],
])

@php
    $elementId = $id ?? ($name ? 'select2-' . preg_replace('/[^a-zA-Z0-9_\-]/', '-', $name) : 'select2-' . uniqid());
    $resolvedAjaxUrl = $ajaxUrl;
    if ($resolvedAjaxUrl && str_starts_with($resolvedAjaxUrl, 'http')) {
        $parsed = parse_url($resolvedAjaxUrl);
        $resolvedAjaxUrl = ($parsed['path'] ?? '') . (isset($parsed['query']) ? '?' . $parsed['query'] : '');
    }

    $resolvedValueLabel = $valueLabel;
    if ((!$resolvedValueLabel || (string)$resolvedValueLabel === (string)$value) && $value !== null && $value !== '') {
        if ($name === 'customer_id' || str_contains((string)$resolvedAjaxUrl, 'customers')) {
            $cust = \App\Models\Customer::find($value);
            if ($cust) {
                $resolvedValueLabel = $cust->full_name . ' (' . $cust->phone_number . ')';
            }
        } elseif ($name === 'service_id' || str_contains((string)$resolvedAjaxUrl, 'services')) {
            $srv = \App\Models\Service::find($value);
            if ($srv) {
                $resolvedValueLabel = $srv->name . ' (Rp ' . number_format($srv->price, 0, ',', '.') . ')';
            }
        }
    }
@endphp

<select
    id="{{ $elementId }}"
    @if ($name) name="{{ $name }}" @endif
    data-tailadmin-select2="true"
    data-placeholder="{{ $placeholder }}"
    @if ($resolvedAjaxUrl) data-ajax-url="{{ $resolvedAjaxUrl }}" @endif
    @if ($dropdownParent) data-dropdown-parent="{{ $dropdownParent }}" @endif
    @if ($required) required @endif
    {{ $attributes->merge(['class' => 'w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white/90']) }}>
    @if ($allowClear)
        <option value=""></option>
    @endif
    @if (!empty($options))
        @foreach ($options as $optVal => $optLabel)
            @php
                $val = is_array($optLabel) ? ($optLabel['id'] ?? $optVal) : $optVal;
                $lbl = is_array($optLabel) ? ($optLabel['text'] ?? $optLabel['name'] ?? $optVal) : $optLabel;
            @endphp
            <option value="{{ $val }}" @selected((string)$value === (string)$val)>
                {{ $lbl }}
            </option>
        @endforeach
    @elseif ($value !== null && $value !== '')
        <option value="{{ $value }}" selected>{{ $resolvedValueLabel ?? $valueLabel ?? $value }}</option>
    @endif
    {{ $slot }}
</select>
