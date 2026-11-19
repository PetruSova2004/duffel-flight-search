@props(['carrier', 'size' => 'size-9'])

@if ($carrier->logoUrl)
    <img src="{{ $carrier->logoUrl }}" alt="{{ $carrier->name }}" loading="lazy" decoding="async"
         class="{{ $size }} shrink-0 rounded-lg bg-white object-contain p-1 ring-1 ring-slate-200">
@else
    <span role="img" aria-label="{{ $carrier->name }}"
          class="{{ $size }} flex shrink-0 items-center justify-center rounded-lg bg-slate-100 text-xs font-bold text-slate-600">
        {{ $carrier->code }}
    </span>
@endif
