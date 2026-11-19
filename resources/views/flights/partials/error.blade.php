<div role="alert" class="rounded-2xl bg-white p-8 text-center shadow-sm ring-1 ring-red-200">
    <div class="mx-auto flex size-12 items-center justify-center rounded-full bg-red-50 text-red-600" aria-hidden="true">
        <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"/>
        </svg>
    </div>
    <h2 class="mt-4 font-semibold text-slate-900">{{ __('flights.error.title') }}</h2>
    <p class="mt-1 text-sm text-slate-600">{{ $message }}</p>
    <x-button type="button" variant="secondary" class="mt-6" x-on:click="retry()">{{ __('flights.error.retry') }}</x-button>
</div>
