<x-layouts.app>
    <section class="mx-auto max-w-3xl py-6 text-center sm:py-12">
        <h1 class="text-3xl font-bold tracking-tight text-slate-900 sm:text-5xl">{{ __('app.home.title') }}</h1>
        <p class="mx-auto mt-4 max-w-2xl text-base text-slate-600 sm:text-lg">{{ __('app.home.lead') }}</p>

        <div class="mt-8 flex flex-col items-stretch justify-center gap-3 sm:flex-row sm:items-center">
            @auth
                <x-button :href="route('flights.index')" class="px-6 py-3 text-base">{{ __('app.home.cta_user') }}</x-button>
            @else
                <x-button :href="route('login')" class="px-6 py-3 text-base">{{ __('app.home.cta_guest_login') }}</x-button>
                <x-button :href="route('register')" variant="secondary" class="px-6 py-3 text-base">{{ __('app.home.cta_guest_register') }}</x-button>
            @endauth
        </div>
    </section>

    <section class="mt-6 grid gap-4 sm:grid-cols-3">
        @foreach (__('app.home.features') as $feature)
            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                <h2 class="font-semibold text-slate-900">{{ $feature['title'] }}</h2>
                <p class="mt-2 text-sm text-slate-600">{{ $feature['text'] }}</p>
            </div>
        @endforeach
    </section>
</x-layouts.app>
