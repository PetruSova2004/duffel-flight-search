@props(['offset'])

@if ($offset !== 0)
    <sup {{ $attributes->class(['ml-0.5 text-xs font-semibold text-amber-700']) }}
         title="{{ match (true) { $offset === 1 => __('flights.offer.next_day'), $offset > 1 => __('flights.offer.days_later', ['count' => $offset]), default => __('flights.offer.previous_day') } }}">{{ sprintf('%+d', $offset) }}</sup>
@endif
