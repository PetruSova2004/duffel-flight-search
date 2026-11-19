@use('App\Support\Format')
@props(['segment'])

@php
    /** @var \App\Data\Flights\Segment $segment */
    $dayOffset = $segment->arrivalDayOffset();
@endphp

<div class="flex gap-3">
    <x-flights.airline-logo :carrier="$segment->marketingCarrier" size="size-7" />

    <div class="min-w-0 flex-1 text-sm">
        <p class="font-medium text-slate-900">
            {{ $segment->flightNumber }} · {{ $segment->marketingCarrier->name }}
            <span class="font-normal text-slate-500">· {{ Format::duration($segment->durationMinutes) }}</span>
        </p>
        @if ($segment->operatingCarrier || $segment->aircraft)
            <p class="text-xs text-slate-500">
                @if ($segment->operatingCarrier){{ __('flights.offer.operated_by', ['carrier' => $segment->operatingCarrier->name]) }}@endif
                @if ($segment->operatingCarrier && $segment->aircraft) · @endif
                @if ($segment->aircraft){{ __('flights.offer.aircraft', ['aircraft' => $segment->aircraft]) }}@endif
            </p>
        @endif

        <div class="mt-2 grid gap-1.5 sm:grid-cols-2 sm:gap-4">
            @foreach (['departure' => $segment->departure, 'arrival' => $segment->arrival] as $kind => $point)
                <div>
                    <span class="font-semibold text-slate-900 tabular-nums">{{ Format::time($point->at) }}</span>
                    <span class="text-slate-600">{{ Format::shortDate($point->at) }}</span>
                    @if ($kind === 'arrival' && $dayOffset !== 0)
                        <span class="text-xs font-semibold text-amber-700">{{ sprintf('%+d', $dayOffset) }}</span>
                    @endif
                    <span class="block text-slate-700">
                        {{ $point->airportName }} ({{ $point->airportCode }})@if ($point->terminal), {{ __('flights.offer.terminal', ['terminal' => $point->terminal]) }}@endif
                    </span>
                    @if ($point->cityName)
                        <span class="block text-xs text-slate-500">{{ $point->cityName }}</span>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</div>
