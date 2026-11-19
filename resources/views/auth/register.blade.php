<x-layouts.app :title="__('auth.register.title')">
    <x-auth-card :title="__('auth.register.title')" :subtitle="__('auth.register.subtitle')">
        <form method="POST" action="{{ route('register.store') }}" class="space-y-5" novalidate>
            @csrf

            <x-input name="name" :label="__('auth.fields.name')" autocomplete="name" maxlength="255" required autofocus />
            <x-input name="email" type="email" :label="__('auth.fields.email')" autocomplete="email" maxlength="255" required />
            <x-input name="password" type="password" :label="__('auth.fields.password')" :hint="__('auth.register.password_hint')"
                     autocomplete="new-password" required />
            <x-input name="password_confirmation" type="password" :label="__('auth.register.password_confirmation')"
                     autocomplete="new-password" required />

            <x-button class="w-full">{{ __('auth.register.submit') }}</x-button>
        </form>

        <x-slot:footer>
            {{ __('auth.register.has_account') }}
            <a href="{{ route('login') }}" class="font-semibold text-brand-600 hover:text-brand-700">{{ __('app.nav.login') }}</a>
        </x-slot:footer>
    </x-auth-card>
</x-layouts.app>
