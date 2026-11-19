@props(['variant' => 'primary', 'size' => 'md', 'href' => null])

@php
    $base = 'inline-flex items-center justify-center gap-2 rounded-lg font-semibold transition '
        . 'disabled:cursor-not-allowed disabled:opacity-60';

    $sizes = [
        'md' => 'px-4 py-2.5 text-sm',
        'lg' => 'px-6 py-3 text-base',
    ];

    $variants = [
        'primary' => 'bg-brand-600 text-white shadow-sm hover:bg-brand-700',
        'secondary' => 'bg-white text-slate-800 ring-1 ring-slate-300 ring-inset hover:bg-slate-50',
        'ghost' => 'text-slate-700 hover:bg-slate-100',
    ];

    $classes = implode(' ', [$base, $sizes[$size] ?? $sizes['md'], $variants[$variant] ?? $variants['primary']]);
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@else
    <button {{ $attributes->merge(['type' => 'submit', 'class' => $classes]) }}>{{ $slot }}</button>
@endif
