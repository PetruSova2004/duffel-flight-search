{{-- JS expressions (options/model/change/disabled/busy/placeholder) run in the parent Alpine scope: pass only fixed developer strings, never user or API data --}}
@props([
    'name',
    'label',
    'options',
    'model',
    'change',
    'disabled' => 'false',
    'busy' => 'false',
    'placeholder' => "''",
    'showCode' => false,
    'limit' => 100,
])

@php
    $id = $name;
    $hasError = $errors->has($name);
@endphp

<div x-data="combobox({
        options: () => {{ $options }},
        value: () => {{ $model }},
        onChange: (code) => { {{ $change }} },
        disabled: () => {{ $disabled }},
        busy: () => {{ $busy }},
        showCode: @js($showCode),
        limit: @js($limit),
     })"
     @focusout="if (! $el.contains($event.relatedTarget)) close()"
     class="relative">
    <label for="{{ $id }}" class="mb-1.5 block text-sm font-medium text-slate-700">{{ $label }}</label>

    <input type="hidden" name="{{ $name }}" :value="value">

    <div class="relative">
        <input id="{{ $id }}" type="text" role="combobox" autocomplete="off" spellcheck="false"
               aria-autocomplete="list" aria-controls="{{ $id }}-listbox"
               :aria-expanded="open.toString()"
               :aria-activedescendant="active >= 0 ? '{{ $id }}-option-' + active : null"
               @if ($hasError) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif
               x-model="query"
               :disabled="isDisabled"
               :placeholder="{{ $placeholder }}"
               @click="show()"
               @input="onInput()"
               @keydown.arrow-down.prevent="move(1)"
               @keydown.arrow-up.prevent="move(-1)"
               @keydown.enter="onEnter($event)"
               @keydown.escape="open && ($event.stopPropagation(), close())"
               @class([
                   'block w-full rounded-lg border bg-white py-2.5 pr-9 pl-3 text-sm shadow-xs placeholder:text-slate-500',
                   'focus:border-brand-500 focus:ring-3 focus:ring-brand-500/35 focus:outline-none',
                   'disabled:cursor-not-allowed disabled:bg-slate-100',
                   'border-red-400' => $hasError,
                   'border-slate-300' => ! $hasError,
               ])>
        <svg class="pointer-events-none absolute top-1/2 right-3 size-4 -translate-y-1/2 text-slate-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path fill-rule="evenodd" d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd"/>
        </svg>
    </div>

    <div x-show="open" x-cloak
         class="absolute z-30 mt-1 w-full overflow-hidden rounded-lg bg-white shadow-lg ring-1 ring-slate-200">
        <ul id="{{ $id }}-listbox" role="listbox" x-ref="list" aria-label="{{ $label }}" class="max-h-64 overflow-y-auto py-1 text-sm">
            <template x-for="(item, index) in visible" :key="item.code">
                <li :id="'{{ $id }}-option-' + index" role="option"
                    :aria-selected="(item.code === value).toString()"
                    :data-active="index === active"
                    @mousedown.prevent="choose(item)"
                    @mousemove="active = index"
                    :class="index === active ? 'bg-brand-50 text-brand-700' : 'text-slate-700'"
                    class="flex cursor-pointer items-center justify-between gap-3 px-3 py-2">
                    <span x-text="item.name" class="truncate"></span>
                    <span x-show="@js($showCode)" x-text="item.code" class="shrink-0 text-xs text-slate-500"></span>
                </li>
            </template>
        </ul>
        <p x-show="isBusy" class="px-3 py-2 text-sm text-slate-500">{{ __('flights.form.cities_loading') }}</p>
        <p x-show="! isBusy && visible.length === 0" class="px-3 py-2 text-sm text-slate-500">{{ __('flights.form.nothing_found') }}</p>
        <p x-show="truncated" class="border-t border-slate-100 px-3 py-2 text-xs text-slate-500">
            {{ __('flights.form.refine', ['count' => $limit]) }}
        </p>
    </div>

    <x-field-error :name="$name" :id="$id.'-error'" />
</div>
