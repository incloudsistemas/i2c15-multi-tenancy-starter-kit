@php
    $tenantAccount = filament()->getTenant();
@endphp

<x-filament-widgets::widget class="fi-filament-info-widget">
    <x-filament::section>
        <div class="flex items-center gap-x-3">
            <x-filament-panels::avatar.user size="lg" :user="$tenantAccount" />

            {{-- <img class="fi-avatar object-cover object-center fi-circular rounded-full h-10 w-10 fi-user-avatar"
                src="{{ CreateThumb(src: $tenantAccount->featured_image?->getUrl(), width: 64, height: 64) }}"
                alt="{{ $tenantAccount->name }}"> --}}

            <div class="flex-1">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ config('app.name') }}
                </p>

                <h2 class="grid flex-1 text-base font-semibold leading-6 text-gray-950 dark:text-white">
                    {{ LimitCharsFromString(string: $tenantAccount->name, numChars: 28) }}
                </h2>
            </div>

            <div class="flex flex-col items-end gap-y-1">
                <p class="text-xs text-gray-500 text-right dark:text-gray-400">
                    <a href="https://incloudsistemas.com.br" rel="noopener noreferrer" target="_blank">
                        <img src="{{ asset('images/desenvolvido-por-incloud.png') }}" alt="desenvolvido por InCloud." />
                    </a>
                    <span>
                        {{ config('app.i2c_pretty_version') }}
                    </span>
                </p>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
