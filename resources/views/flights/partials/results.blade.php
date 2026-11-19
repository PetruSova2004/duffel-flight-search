@use('App\Support\Format')

@if ($result->isEmpty())
    <div class="rounded-2xl bg-white p-8 text-center shadow-sm ring-1 ring-slate-200">
        <p class="font-semibold text-slate-900">{{ __('flights.empty.title') }}</p>
        <p class="mt-1 text-sm text-slate-600">{{ __('flights.empty.text') }}</p>
    </div>
@else
    <div x-data="offerSort">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">
                    {{ __('flights.results.route', [
                        'from' => $result->originName,
                        'to' => $result->destinationName,
                        'date' => Format::date($result->departureDate),
                    ]) }}
                </h2>
                <p class="text-sm text-slate-600">
                    {{ trans_choice('flights.results.count', count($result->offers)) }}.
                    {{ __('flights.results.local_time') }}
                </p>
            </div>

            <div class="flex items-center gap-2">
                <label for="sort" class="text-sm text-slate-600">{{ __('flights.results.sort') }}</label>
                <select id="sort" x-model="sort" @change="apply()"
                        class="rounded-lg border border-slate-300 bg-white py-2 pr-8 pl-3 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-100 focus:outline-none">
                    <option value="price">{{ __('flights.results.sort_price') }}</option>
                    <option value="departure">{{ __('flights.results.sort_departure') }}</option>
                    <option value="duration">{{ __('flights.results.sort_duration') }}</option>
                </select>
            </div>
        </div>

        <ul x-ref="offers" class="mt-4 space-y-4">
            @foreach ($result->offers as $offer)
                <x-flights.offer-card :offer="$offer" />
            @endforeach
        </ul>
    </div>
@endif
