<x-layouts.app :title="__('auth.password.title')">
    <x-auth-card :title="__('auth.password.title')" :subtitle="__('auth.password.subtitle')">
        <form method="POST" action="{{ route('password.update') }}" class="space-y-5" novalidate>
            @csrf
            @method('PUT')

            <x-input name="current_password" type="password" :label="__('auth.password.current')" autocomplete="current-password" required />
            <x-input name="password" type="password" :label="__('auth.password.new')" :hint="__('auth.register.password_hint')"
                     autocomplete="new-password" required />
            <x-input name="password_confirmation" type="password" :label="__('auth.password.confirmation')"
                     autocomplete="new-password" required />

            <x-button class="w-full">{{ __('auth.password.submit') }}</x-button>
        </form>
    </x-auth-card>
</x-layouts.app>
