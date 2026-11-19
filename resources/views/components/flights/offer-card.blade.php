@use('App\Support\Format')
@props(['offer'])

@php
    /** @var \App\Data\Flights\Offer $offer */
    $departure = $offer->departure();
    $arrival = $offer->arrival();
    $dayOffset = $offer->arrivalDayOffset();
    $checked = $offer->baggageCount('checked');
    $carryOn = $offer->baggageCount('carry_on');
@endphp

<li data-price="{{ $offer->price->amount }}"
    data-departure="{{ $departure->at->getTimestamp() }}"
    data-duration="{{ $offer->durationMinutes }}">
    <article class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
        <div class="grid gap-4 p-4 sm:p-5 md:grid-cols-[1fr_auto] md:items-center md:gap-6">
            <div class="min-w-0">
                <div class="flex items-center gap-3">
                    <x-flights.airline-logo :carrier="$offer->airline" />
                    <div class="min-w-0">
                        <p class="truncate text-sm font-semibold text-slate-900" title="{{ $offer->airline->name }}">{{ $offer->airline->name }}</p>
                        <p class="truncate text-xs text-slate-500">{{ implode(', ', $offer->flightNumbers()) }}</p>
                    </div>
                </div>

                <div class="mt-4 flex items-center gap-3 sm:gap-5">
                    <div class="shrink-0">
                        <p class="text-2xl font-bold text-slate-900 tabular-nums">{{ Format::time($departure->at) }}</p>
                        <p class="text-xs text-slate-600"><abbr title="{{ $departure->airportName }}" class="no-underline">{{ $departure->airportCode }}</abbr> · {{ Format::shortDate($departure->at) }}</p>
                    </div>

                    <div class="min-w-0 flex-1 text-center">
                        <p class="text-xs font-medium text-slate-700">{{ Format::duration($offer->durationMinutes) }}</p>
                        <div class="relative my-1.5 h-px bg-slate-300" aria-hidden="true">
                            @foreach ($offer->layovers as $layover)
                                <span class="absolute top-1/2 size-2 -translate-x-1/2 -translate-y-1/2 rounded-full bg-amber-500 ring-2 ring-white"
                                      style="left: {{ round(100 * $loop->iteration / (count($offer->layovers) + 1)) }}%"></span>
                            @endforeach
                        </div>
                        @if ($offer->layovers === [])
                            <p class="text-xs text-emerald-700">{{ __('flights.offer.direct') }}</p>
                        @else
                            <p class="text-xs text-amber-700">
                                {{ trans_choice('flights.offer.stops', count($offer->layovers)) }}:
                                {{ collect($offer->layovers)->map(fn ($l) => $l->airportCode.' '.Format::duration($l->durationMinutes))->implode(', ') }}
                            </p>
                        @endif
                    </div>

                    <div class="shrink-0 text-right">
                        <p class="text-2xl font-bold text-slate-900 tabular-nums">
                            {{ Format::time($arrival->at) }}<x-flights.day-offset :offset="$dayOffset" />
                        </p>
                        <p class="text-xs text-slate-600"><abbr title="{{ $arrival->airportName }}" class="no-underline">{{ $arrival->airportCode }}</abbr> · {{ Format::shortDate($arrival->at) }}</p>
                    </div>
                </div>
            </div>

            <div class="flex items-end justify-between gap-4 border-t border-slate-100 pt-3 md:block md:border-t-0 md:border-l md:pt-0 md:pl-6 md:text-right">
                <div>
                    <p class="text-2xl font-bold text-brand-700 tabular-nums">{{ Format::money($offer->price) }}</p>
                    <p class="text-xs text-slate-500">{{ __('flights.offer.per_adult') }}@if ($offer->cabinClass) · {{ Format::cabin($offer->cabinClass) }}@endif</p>
                </div>
                @if ($offer->expiresAt?->isFuture())
                    <p class="text-xs text-slate-500 md:mt-1" title="{{ Format::date($offer->expiresAt->setTimezone(config('app.timezone'))) }}, {{ Format::time($offer->expiresAt->setTimezone(config('app.timezone'))) }}">
                        {{ __('flights.offer.expires', ['time' => $offer->expiresAt->locale(app()->getLocale())->diffForHumans(syntax: \Carbon\CarbonInterface::DIFF_ABSOLUTE)]) }}
                    </p>
                @endif
            </div>
        </div>

        <ul class="flex flex-wrap gap-2 px-4 pb-4 text-xs sm:px-5" aria-label="{{ __('flights.offer.details') }}">
            @if ($offer->fareBrand)
                <li class="rounded-full bg-brand-50 px-2.5 py-1 font-medium text-brand-700">{{ $offer->fareBrand }}</li>
            @endif
            <li @class(['rounded-full px-2.5 py-1', 'bg-emerald-50 text-emerald-800' => $checked > 0, 'bg-slate-100 text-slate-600' => $checked === 0])>
                {{ $checked > 0 ? __('flights.offer.checked_bag', ['count' => $checked]) : __('flights.offer.no_checked_bag') }}
            </li>
            <li @class(['rounded-full px-2.5 py-1', 'bg-emerald-50 text-emerald-800' => $carryOn > 0, 'bg-slate-100 text-slate-600' => $carryOn === 0])>
                {{ $carryOn > 0 ? __('flights.offer.carry_on', ['count' => $carryOn]) : __('flights.offer.no_carry_on') }}
            </li>
            @if ($offer->co2Kg !== null)
                <li class="rounded-full bg-slate-100 px-2.5 py-1 text-slate-600">{{ __('flights.offer.co2', ['kg' => $offer->co2Kg]) }}</li>
            @endif
        </ul>

        <details class="group border-t border-slate-100">
            <summary class="flex cursor-pointer list-none items-center gap-1 px-4 py-3 text-sm font-medium text-brand-600 hover:text-brand-700 sm:px-5 [&::-webkit-details-marker]:hidden">
                <span class="group-open:hidden">{{ __('flights.offer.details') }}</span>
                <span class="hidden group-open:inline">{{ __('flights.offer.hide_details') }}</span>
                <svg class="size-4 transition group-open:rotate-180" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd"/>
                </svg>
            </summary>

            <ol class="space-y-4 px-4 pb-5 sm:px-5">
                @foreach ($offer->segments as $segment)
                    <li>
                        <x-flights.segment :segment="$segment" />
                    </li>

                    @if ($layover = $offer->layovers[$loop->index] ?? null)
                        <li class="rounded-lg bg-amber-50 px-3 py-2 text-sm text-amber-900">
                            {{ __('flights.offer.layover', [
                                'place' => $layover->cityName ? $layover->cityName.' ('.$layover->airportCode.')' : $layover->airportCode,
                                'duration' => Format::duration($layover->durationMinutes),
                            ]) }}@if ($layover->changesAirport), <strong>{{ __('flights.offer.airport_change') }}</strong>@endif
                        </li>
                    @endif
                @endforeach
            </ol>
        </details>
    </article>
</li>
