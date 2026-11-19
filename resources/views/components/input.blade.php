@props(['name', 'label', 'type' => 'text', 'hint' => null, 'value' => null])

@php
    $id = $attributes->get('id', $name);
    $isPassword = $type === 'password';
    $hasError = $errors->has($name);
    $describedBy = collect([$hint ? $id.'-hint' : null, $hasError ? $id.'-error' : null])->filter()->implode(' ');
@endphp

<div>
    <label for="{{ $id }}" class="mb-1.5 block text-sm font-medium text-slate-700">{{ $label }}</label>

    <div class="relative" @if ($isPassword) x-data="{ show: false }" @endif>
        <input
            id="{{ $id }}"
            name="{{ $name }}"
            type="{{ $type }}"
            @if ($isPassword) :type="show ? 'text' : 'password'" @else value="{{ old($name, $value) }}" @endif
            @if ($hasError) aria-invalid="true" @endif
            @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
            {{ $attributes->except('id')->class([
                'block w-full rounded-lg border bg-white px-3 py-2.5 text-sm shadow-xs placeholder:text-slate-400',
                'focus:border-brand-500 focus:ring-2 focus:ring-brand-100 focus:outline-none',
                'pr-11' => $isPassword,
                'border-red-400' => $hasError,
                'border-slate-300' => ! $hasError,
            ]) }}
        >

        @if ($isPassword)
            <button type="button" x-cloak @click="show = ! show" :aria-pressed="show.toString()"
                    :aria-label="show ? @js(__('app.password.hide')) : @js(__('app.password.show'))"
                    class="absolute inset-y-0 right-0 flex w-11 items-center justify-center rounded-r-lg text-slate-500 hover:text-slate-800">
                <svg x-show="! show" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.04 12.32a1 1 0 0 1 0-.64C3.42 7.51 7.36 4.5 12 4.5s8.57 3 9.96 7.18a1 1 0 0 1 0 .64C20.58 16.49 16.64 19.5 12 19.5s-8.57-3-9.96-7.18Z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                </svg>
                <svg x-show="show" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.22A10.5 10.5 0 0 0 1.93 12C3.23 16.34 7.24 19.5 12 19.5c.99 0 1.95-.14 2.86-.4M6.23 6.23A10.45 10.45 0 0 1 12 4.5c4.76 0 8.77 3.16 10.07 7.5a10.52 10.52 0 0 1-4.29 5.77M6.23 6.23 3 3m3.23 3.23 3.65 3.65m7.89 7.89L21 21m-3.23-3.23-3.65-3.65m0 0a3 3 0 1 0-4.24-4.24m4.24 4.24L9.88 9.88"/>
                </svg>
            </button>
        @endif
    </div>

    @if ($hint)
        <p id="{{ $id }}-hint" class="mt-1.5 text-xs text-slate-500">{{ $hint }}</p>
    @endif

    <x-field-error :name="$name" :id="$id.'-error'" />
</div>
