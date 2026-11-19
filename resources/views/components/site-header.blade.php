<header x-data="{ open: false }" @keydown.escape.window="open = false" class="sticky top-0 z-40 border-b border-slate-200 bg-white/90 backdrop-blur">
    <div class="mx-auto flex h-16 max-w-6xl items-center justify-between gap-4 px-4 sm:px-6">
        <a href="{{ route('home') }}" class="flex items-center gap-2 text-lg font-semibold text-slate-900">
            <svg class="size-7 text-brand-600" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M21 16v-2l-8-5V3.5a1.5 1.5 0 0 0-3 0V9l-8 5v2l8-2.5V19l-2 1.5V22l3.5-1 3.5 1v-1.5L13 19v-5.5l8 2.5Z"/>
            </svg>
            {{ config('app.name') }}
        </a>

        <nav class="hidden items-center gap-1 md:flex" aria-label="{{ __('app.nav.menu') }}">
            <x-nav-link route="home">{{ __('app.nav.home') }}</x-nav-link>
            @auth
                <x-nav-link route="flights.index">{{ __('app.nav.flights') }}</x-nav-link>
            @endauth
        </nav>

        <div class="hidden items-center gap-2 md:flex">
            @auth
                <a href="{{ route('password.edit') }}" title="{{ __('app.nav.password') }}"
                   class="max-w-48 truncate rounded-lg px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100">
                    {{ auth()->user()->name }}
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-button variant="secondary">{{ __('app.nav.logout') }}</x-button>
                </form>
            @else
                <x-nav-link route="login">{{ __('app.nav.login') }}</x-nav-link>
                <x-button :href="route('register')" :aria-current="request()->routeIs('register') ? 'page' : null"
                          @class(['ring-2 ring-brand-100 ring-offset-1' => request()->routeIs('register')])>
                    {{ __('app.nav.register') }}
                </x-button>
            @endauth
        </div>

        <button type="button" class="rounded-lg p-2 text-slate-700 hover:bg-slate-100 md:hidden"
                @click="open = ! open" :aria-expanded="open.toString()" aria-expanded="false" aria-controls="mobile-menu">
            <span class="sr-only">{{ __('app.nav.menu') }}</span>
            <svg x-show="! open" class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
            <svg x-show="open" x-cloak class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <path stroke-linecap="round" d="M6 6l12 12M18 6 6 18"/>
            </svg>
        </button>
    </div>

    <nav id="mobile-menu" x-show="open" x-cloak x-transition.opacity class="border-t border-slate-200 bg-white md:hidden"
         aria-label="{{ __('app.nav.menu') }}">
        <div class="space-y-1 px-4 py-3">
            <x-nav-link route="home" mobile>{{ __('app.nav.home') }}</x-nav-link>
            @auth
                <x-nav-link route="flights.index" mobile>{{ __('app.nav.flights') }}</x-nav-link>
                <div class="mt-2 border-t border-slate-100 pt-3">
                    <p class="truncate px-3 pb-1 text-xs text-slate-500">{{ auth()->user()->name }}</p>
                    <x-nav-link route="password.edit" mobile>{{ __('app.nav.password') }}</x-nav-link>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="block w-full rounded-lg px-3 py-2 text-left text-sm font-medium text-slate-600 hover:bg-slate-100">
                            {{ __('app.nav.logout') }}
                        </button>
                    </form>
                </div>
            @else
                <x-nav-link route="login" mobile>{{ __('app.nav.login') }}</x-nav-link>
                <x-nav-link route="register" mobile>{{ __('app.nav.register') }}</x-nav-link>
            @endauth
        </div>
    </nav>
</header>
