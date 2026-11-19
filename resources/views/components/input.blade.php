@props(['name', 'label', 'type' => 'text', 'hint' => null, 'value' => null])

@php
    $id = $attributes->get('id', $name);
    $hasError = $errors->has($name);
    $describedBy = collect([$hint ? $id.'-hint' : null, $hasError ? $id.'-error' : null])->filter()->implode(' ');
@endphp

<div>
    <label for="{{ $id }}" class="mb-1.5 block text-sm font-medium text-slate-700">{{ $label }}</label>

    <input
        id="{{ $id }}"
        name="{{ $name }}"
        type="{{ $type }}"
        @if ($type !== 'password') value="{{ old($name, $value) }}" @endif
        @if ($hasError) aria-invalid="true" @endif
        @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
        {{ $attributes->except('id')->class([
            'block w-full rounded-lg border bg-white px-3 py-2.5 text-sm shadow-xs placeholder:text-slate-400',
            'focus:border-brand-500 focus:ring-2 focus:ring-brand-100 focus:outline-none',
            'border-red-400' => $hasError,
            'border-slate-300' => ! $hasError,
        ]) }}
    >

    @if ($hint)
        <p id="{{ $id }}-hint" class="mt-1.5 text-xs text-slate-500">{{ $hint }}</p>
    @endif

    <x-field-error :name="$name" :id="$id.'-error'" />
</div>
