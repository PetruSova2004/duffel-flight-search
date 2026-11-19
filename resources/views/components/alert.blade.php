@props(['type' => 'info'])

@php
    $styles = [
        'info' => 'bg-brand-50 text-brand-700 ring-brand-100',
        'success' => 'bg-emerald-50 text-emerald-800 ring-emerald-200',
        'error' => 'bg-red-50 text-red-800 ring-red-200',
    ];
@endphp

<div role="{{ $type === 'error' ? 'alert' : 'status' }}"
     {{ $attributes->class(['rounded-lg px-4 py-3 text-sm ring-1 ring-inset', $styles[$type] ?? $styles['info']]) }}>
    {{ $slot }}
</div>
