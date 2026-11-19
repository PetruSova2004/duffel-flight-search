<form method="GET" action="{{ route('flights.search') }}" @submit="submit($event)" novalidate
      class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200 sm:p-6">
    <div class="grid gap-x-4 gap-y-5 md:grid-cols-2">
        <fieldset class="grid gap-4 sm:grid-cols-2">
            <legend class="mb-3 text-sm font-semibold text-slate-900">{{ __('flights.form.from') }}</legend>
            <x-combobox name="origin_country" :label="__('flights.form.origin_country')"
                        options="countries" model="origin_country" change="setCountry('origin', code)"
                        placeholder="messages.countryPlaceholder" />
            <x-combobox name="origin" :label="__('flights.form.origin')" show-code
                        options="cities.origin" model="origin" change="origin = code" disabled="cityDisabled('origin')" busy="loading.origin"
                        placeholder="cityPlaceholder('origin')" />
        </fieldset>

        <fieldset class="grid gap-4 sm:grid-cols-2">
            <legend class="mb-3 text-sm font-semibold text-slate-900">{{ __('flights.form.to') }}</legend>
            <x-combobox name="destination_country" :label="__('flights.form.destination_country')"
                        options="countries" model="destination_country" change="setCountry('destination', code)"
                        placeholder="messages.countryPlaceholder" />
            <x-combobox name="destination" :label="__('flights.form.destination')" show-code
                        options="cities.destination" model="destination" change="destination = code" disabled="cityDisabled('destination')" busy="loading.destination"
                        placeholder="cityPlaceholder('destination')" />
        </fieldset>
    </div>

    <p x-show="citiesError" x-text="citiesError" x-cloak role="alert" class="mt-4 text-sm text-red-600"></p>

    <div class="mt-5 grid items-end gap-4 border-t border-slate-100 pt-5 sm:grid-cols-[minmax(0,14rem)_1fr_auto]">
        <x-input name="departure_date" type="date" :label="__('flights.form.date')" required
                 :value="request('departure_date')"
                 :min="$minDate" :max="$maxDate" />

        <p class="text-sm text-slate-500 sm:pb-3">{{ __('flights.form.passengers_note') }}</p>

        <x-button class="w-full sm:w-auto sm:min-w-44" ::disabled="submitting">
            <svg x-show="submitting" x-cloak class="size-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" class="opacity-25"/>
                <path d="M22 12a10 10 0 0 0-10-10" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
            </svg>
            <span x-text="submitting ? @js(__('flights.form.searching')) : @js(__('flights.form.submit'))">{{ __('flights.form.submit') }}</span>
        </x-button>
    </div>
</form>
