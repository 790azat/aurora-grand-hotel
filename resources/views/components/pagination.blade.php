{{-- Livewire pagination view: $paginator->links('components.pagination') --}}
@if ($paginator->hasPages())
    @php $pageName = $paginator->getPageName(); @endphp
    <nav role="navigation" aria-label="{{ __('site.common.pagination') }}" class="flex items-center justify-center gap-2">
        <button type="button" wire:click="previousPage('{{ $pageName }}')" wire:loading.attr="disabled" @disabled($paginator->onFirstPage())
                x-on:click="document.getElementById('{{ $scrollTo ?? 'main' }}')?.scrollIntoView({ behavior: 'smooth' })"
                class="grid size-11 place-items-center rounded-full border border-line transition hover:border-gold-500 hover:text-gold-600 disabled:pointer-events-none disabled:opacity-40" aria-label="{{ __('site.common.previous') }}">
            <x-heroicon-o-chevron-left class="size-4" />
        </button>
        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="px-2 text-muted">…</span>
            @endif
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span aria-current="page" class="grid size-11 place-items-center rounded-full bg-midnight-900 text-sm font-semibold text-white dark:bg-gold-400 dark:text-midnight-950">{{ $page }}</span>
                    @else
                        <button type="button" wire:click="gotoPage({{ $page }}, '{{ $pageName }}')"
                                x-on:click="document.getElementById('{{ $scrollTo ?? 'main' }}')?.scrollIntoView({ behavior: 'smooth' })"
                                class="grid size-11 place-items-center rounded-full border border-line text-sm transition hover:border-gold-500 hover:text-gold-600">{{ $page }}</button>
                    @endif
                @endforeach
            @endif
        @endforeach
        <button type="button" wire:click="nextPage('{{ $pageName }}')" wire:loading.attr="disabled" @disabled(! $paginator->hasMorePages())
                x-on:click="document.getElementById('{{ $scrollTo ?? 'main' }}')?.scrollIntoView({ behavior: 'smooth' })"
                class="grid size-11 place-items-center rounded-full border border-line transition hover:border-gold-500 hover:text-gold-600 disabled:pointer-events-none disabled:opacity-40" aria-label="{{ __('site.common.next') }}">
            <x-heroicon-o-chevron-right class="size-4" />
        </button>
    </nav>
@endif
