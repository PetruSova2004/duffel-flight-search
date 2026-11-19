<x-layouts.app :title="__('flights.title')">
    <h1 class="text-2xl font-bold text-slate-900 sm:text-3xl">{{ __('flights.title') }}</h1>
    <p class="mt-1 text-slate-600">{{ __('flights.subtitle') }}</p>

    <noscript>
        <x-alert type="error" class="mt-6">{{ __('flights.no_js') }}</x-alert>
    </noscript>

    @isset($formError)
        <x-alert type="error" class="mt-6">
            {{ $formError }}
            <a href="{{ route('flights.index') }}" class="ml-1 font-semibold underline">{{ __('flights.error.reload') }}</a>
        </x-alert>
    @else
        <section class="mt-6"
                 x-data="flightSearch({
                     citiesUrl: @js(route('flights.cities')),
                     countries: @js($countries),
                     values: @js(old() ?: request()->only(['origin_country', 'origin', 'destination_country', 'destination'])),
                     messages: {
                         countryPlaceholder: @js(__('flights.form.country_placeholder')),
                         cityPlaceholder: @js(__('flights.form.city_placeholder')),
                         cityDisabled: @js(__('flights.form.city_disabled')),
                         citiesLoading: @js(__('flights.form.cities_loading')),
                         citiesError: @js(__('flights.form.cities_error')),
                     },
                 })">
            @include('flights.partials.form')

            <div class="mt-8" x-show="submitting" x-cloak>
                @include('flights.partials.loading')
            </div>

            <div class="mt-8" x-show="! submitting">
                @isset($searchError)
                    @include('flights.partials.error', ['message' => $searchError])
                @elseif (isset($result))
                    @include('flights.partials.results', ['result' => $result])
                @endisset
            </div>
        </section>
    @endisset
</x-layouts.app>
