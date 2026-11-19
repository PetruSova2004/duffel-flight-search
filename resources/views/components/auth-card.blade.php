@props(['title', 'subtitle' => null])

<div class="mx-auto w-full max-w-md">
    <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 sm:p-8">
        <h1 class="text-2xl font-bold text-slate-900">{{ $title }}</h1>
        @if ($subtitle)
            <p class="mt-1 text-sm text-slate-600">{{ $subtitle }}</p>
        @endif

        <div class="mt-6">
            {{ $slot }}
        </div>
    </div>

    @isset($footer)
        <p class="mt-4 text-center text-sm text-slate-600">{{ $footer }}</p>
    @endisset
</div>
