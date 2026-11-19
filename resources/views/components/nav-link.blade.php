@props(['route', 'active' => null, 'mobile' => false])

@php
    $active = request()->routeIs($active ?? $route);
@endphp

<a href="{{ route($route) }}"
   @if ($active) aria-current="page" @endif
   {{ $attributes->class([
       'rounded-lg px-3 py-2 text-sm font-medium transition',
       'block' => $mobile,
       'bg-brand-50 text-brand-700' => $active,
       'text-slate-600 hover:bg-slate-100 hover:text-slate-900' => ! $active,
   ]) }}>
    {{ $slot }}
</a>
