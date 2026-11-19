@props(['variant' => 'primary', 'href' => null])

@php
    $base = 'inline-flex items-center justify-center gap-2 rounded-lg px-4 py-2.5 text-sm font-semibold transition '
        . 'disabled:cursor-not-allowed disabled:opacity-60';

    $variants = [
        'primary' => 'bg-brand-600 text-white shadow-sm hover:bg-brand-700',
        'secondary' => 'bg-white text-slate-800 ring-1 ring-slate-300 ring-inset hover:bg-slate-50',
        'ghost' => 'text-slate-700 hover:bg-slate-100',
    ];

    $classes = $base.' '.($variants[$variant] ?? $variants['primary']);
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@else
    <button {{ $attributes->merge(['type' => 'submit', 'class' => $classes]) }}>{{ $slot }}</button>
@endif
