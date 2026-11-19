<div class="rounded-2xl bg-white p-8 text-center shadow-sm ring-1 ring-slate-200">
    <svg class="mx-auto size-10 animate-spin text-brand-600" viewBox="0 0 24 24" fill="none" aria-hidden="true">
        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" class="opacity-20"/>
        <path d="M22 12a10 10 0 0 0-10-10" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
    </svg>
    <h2 class="mt-4 font-semibold text-slate-900">{{ __('flights.loading.title') }}</h2>
    <p class="mt-1 text-sm text-slate-600">{{ __('flights.loading.text') }}</p>

    <div class="mx-auto mt-6 max-w-2xl space-y-3" aria-hidden="true">
        @foreach (range(1, 3) as $i)
            <div class="h-20 animate-pulse rounded-xl bg-slate-100"></div>
        @endforeach
    </div>
</div>
