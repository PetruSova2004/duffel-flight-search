<x-layouts.app :title="__('auth.login.title')">
    <x-auth-card :title="__('auth.login.title')" :subtitle="__('auth.login.subtitle')">
        <form method="POST" action="{{ route('login.store') }}" class="space-y-5" novalidate>
            @csrf

            <x-input name="email" type="email" :label="__('auth.fields.email')" autocomplete="email" required autofocus />
            <x-input name="password" type="password" :label="__('auth.fields.password')" autocomplete="current-password" required />

            <label class="flex items-center gap-2 text-sm text-slate-700">
                <input type="checkbox" name="remember" value="1" @checked(old('remember'))
                       class="size-4 accent-brand-600">
                {{ __('auth.login.remember') }}
            </label>

            <x-button class="w-full">{{ __('auth.login.submit') }}</x-button>
        </form>

        <x-slot:footer>
            {{ __('auth.login.no_account') }}
            <a href="{{ route('register') }}" class="font-semibold text-brand-600 hover:text-brand-700">{{ __('app.nav.register') }}</a>
        </x-slot:footer>
    </x-auth-card>
</x-layouts.app>
