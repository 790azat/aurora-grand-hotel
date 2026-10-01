{{-- Split-screen auth shell: @component('livewire.auth.partials.shell', ['title' => …, 'subtitle' => …]) … @endcomponent --}}
<div class="grid min-h-[calc(100vh-4.5rem)] lg:grid-cols-2">
    <div class="relative hidden overflow-hidden bg-midnight-900 lg:block">
        <img src="{{ \Database\Seeders\DatabaseSeeder::img('1582719508461-905c673771fd', 1400) }}" alt="" class="absolute inset-0 size-full object-cover opacity-70">
        <div class="absolute inset-0 bg-gradient-to-t from-midnight-950 via-midnight-950/40 to-midnight-950/10"></div>
        <div class="absolute inset-x-12 bottom-14 text-white">
            <p class="eyebrow text-gold-300">{{ setting('hotel_name') }}</p>
            <p class="mt-3 max-w-md font-serif text-4xl leading-tight">{{ __('account.auth.quote') }}</p>
            <ul class="mt-8 space-y-2 text-sm text-gray-300">
                @foreach (__('account.auth.perks') as $perk)
                    <li class="flex items-center gap-2">@include('livewire.booking.partials.icon', ['name' => 'check', 'class' => 'size-4 text-gold-400', 'stroke' => 2]) {{ $perk }}</li>
                @endforeach
            </ul>
        </div>
    </div>
    <div class="flex items-center justify-center px-4 py-12 sm:px-8">
        <div class="w-full max-w-md">
            <h1 class="font-serif text-4xl sm:text-5xl">{{ $title }}</h1>
            @isset($subtitle)<p class="mt-2 text-sm text-muted">{{ $subtitle }}</p>@endisset
            <div class="mt-8">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>
