@props(['code'])

<x-layouts.app :title="__('app.errors.'.$code.'.title')">
    <section class="mx-auto max-w-lg py-12 text-center sm:py-20">
        <p class="text-6xl font-bold text-brand-600">{{ $code }}</p>
        <h1 class="mt-4 text-2xl font-bold text-slate-900">{{ __('app.errors.'.$code.'.title') }}</h1>
        <p class="mt-2 text-slate-600">{{ __('app.errors.'.$code.'.text') }}</p>
        <x-button :href="route('home')" class="mt-8">{{ __('app.errors.back_home') }}</x-button>
    </section>
</x-layouts.app>
