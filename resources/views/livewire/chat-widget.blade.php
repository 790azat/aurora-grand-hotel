@php
    $guestCount = $messages->where('sender', 'guest')->count();
    $showIntro = ! $conversation && ! auth()->check();
@endphp
<div x-data="{
        scroll(smooth = false) {
            this.$nextTick(() => { const el = this.$refs.thread; if (el) el.scrollTo({ top: el.scrollHeight, behavior: smooth ? 'smooth' : 'auto' }) })
        },
        toggle() {
            $wire.open = ! $wire.open;
            $wire.$commit();
            if ($wire.open) { this.scroll(); setTimeout(() => this.$refs.input?.focus({ preventScroll: true }), 150) }
        },
        grow(el) { el.style.height = 'auto'; el.style.height = Math.min(el.scrollHeight, 128) + 'px' },
     }"
     x-init="scroll()"
     @keydown.escape.window="if ($wire.open) toggle()"
     class="chat-widget">

    {{-- Polling: fast while the panel is open, slow (unread dot only) while closed. --}}
    @if ($open)
        <div wire:poll.4s="poll" class="hidden"></div>
    @elseif ($conversation)
        <div wire:poll.15s="poll" class="hidden"></div>
    @endif

    {{-- Launcher --}}
    <button type="button" @click="toggle()"
            :class="$wire.open ? 'max-sm:hidden' : ''"
            class="group fixed right-5 bottom-5 z-40 grid size-14 place-items-center rounded-full border border-white/25 bg-gradient-to-br from-gold-400 to-gold-600 text-midnight-950 shadow-xl shadow-gold-900/30 transition hover:scale-105 focus:outline-none focus-visible:ring-2 focus-visible:ring-gold-400 focus-visible:ring-offset-2 focus-visible:ring-offset-page dark:shadow-black/40"
            :aria-expanded="$wire.open ? 'true' : 'false'" aria-controls="chat-panel"
            aria-label="{{ $open ? __('chat.widget.close') : __('chat.widget.open') }}">
        <x-heroicon-o-chat-bubble-left-right class="size-7 transition" ::class="$wire.open ? 'scale-0 opacity-0' : ''" />
        <x-heroicon-o-x-mark class="absolute size-7 scale-0 opacity-0 transition" ::class="$wire.open ? 'scale-100! opacity-100!' : ''" />
        @if ($unread)
            <span class="absolute -top-0.5 -right-0.5 grid min-w-5 place-items-center rounded-full bg-rose-500 px-1 text-[11px] leading-5 font-bold text-white ring-2 ring-page" aria-label="{{ __('chat.widget.new_message') }}">{{ $unread }}</span>
        @endif
        <span class="pointer-events-none absolute right-full mr-3 hidden whitespace-nowrap rounded-full bg-midnight-900 px-3 py-1.5 text-xs font-semibold text-white opacity-0 shadow-lg transition group-hover:opacity-100 lg:block dark:bg-surface dark:text-ink" x-show="! $wire.open">{{ __('chat.widget.open') }}</span>
    </button>

    {{-- Panel --}}
    <section id="chat-panel" x-show="$wire.open" x-cloak
             x-transition:enter="transition duration-200 ease-out" x-transition:enter-start="opacity-0 translate-y-4 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="transition duration-150 ease-in" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0 translate-y-4"
             role="dialog" aria-modal="false" aria-labelledby="chat-title"
             class="fixed inset-x-0 top-3 bottom-0 z-[55] flex flex-col overflow-hidden rounded-t-3xl border border-line bg-page shadow-2xl sm:inset-x-auto sm:top-auto sm:right-5 sm:bottom-24 sm:h-[min(640px,calc(100dvh-8rem))] sm:w-[380px] sm:rounded-3xl dark:shadow-black/50">

        {{-- Header --}}
        <header class="relative flex items-center gap-3 bg-midnight-900 px-4 py-3.5 text-white">
            <div class="relative shrink-0">
                <div class="grid size-11 place-items-center rounded-full border border-gold-400/50 bg-gradient-to-br from-midnight-800 to-gold-900 font-serif text-xl font-semibold text-gold-300">A</div>
                <span class="absolute right-0 bottom-0 size-3 rounded-full bg-emerald-400 ring-2 ring-midnight-900"></span>
            </div>
            <div class="min-w-0 flex-1 leading-tight">
                <h2 id="chat-title" class="font-sans text-[15px] font-semibold tracking-normal">{{ __('chat.widget.title') }} <span class="font-normal text-emerald-300">· {{ __('chat.widget.online') }}</span></h2>
                <p class="mt-0.5 truncate text-xs text-gray-400">{{ __('chat.widget.replies_fast') }}</p>
            </div>
            <button type="button" @click="toggle()" class="grid size-9 shrink-0 place-items-center rounded-full text-gray-300 transition hover:bg-white/10 hover:text-white" aria-label="{{ __('chat.widget.close') }}">
                <x-heroicon-o-chevron-down class="size-5 max-sm:hidden" />
                <x-heroicon-o-x-mark class="size-5 sm:hidden" />
            </button>
            <div class="pointer-events-none absolute inset-x-0 bottom-0 h-px bg-gradient-to-r from-transparent via-gold-400/50 to-transparent"></div>
        </header>

        {{-- Thread --}}
        <div x-ref="thread" x-init="new MutationObserver(() => scroll(true)).observe($el, { childList: true, subtree: true })"
             class="flex-1 space-y-3 overflow-y-auto overscroll-contain px-4 py-4" aria-live="polite">

            {{-- Welcome --}}
            <div class="flex items-end gap-2">
                <div class="grid size-7 shrink-0 place-items-center rounded-full bg-midnight-900 font-serif text-sm font-semibold text-gold-300 dark:bg-gold-900/70">A</div>
                <div class="max-w-[82%]">
                    <p class="mb-1 text-[11px] font-semibold text-muted">{{ __('chat.widget.bot') }}</p>
                    <div class="rounded-2xl rounded-bl-md border border-line bg-surface px-3.5 py-2.5 text-sm leading-relaxed text-ink shadow-sm">{{ __('chat.widget.welcome') }}</div>
                </div>
            </div>

            @if ($guestCount === 0)
                <div class="pl-9">
                    <p class="mb-2 text-[11px] font-semibold uppercase tracking-wider text-muted">{{ __('chat.widget.quick_title') }}</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach (['prices' => 'banknotes', 'checkin' => 'clock', 'transfer' => 'paper-airplane', 'book' => 'calendar-days'] as $topic => $icon)
                            <button type="button" wire:click="ask('{{ $topic }}')" wire:loading.attr="disabled"
                                    class="inline-flex items-center gap-1.5 rounded-full border border-gold-400/50 bg-gold-50 px-3 py-1.5 text-xs font-semibold text-gold-800 transition hover:border-gold-500 hover:bg-gold-100 disabled:opacity-50 dark:border-gold-400/30 dark:bg-gold-900/30 dark:text-gold-200 dark:hover:bg-gold-900/60">
                                <x-dynamic-component :component="'heroicon-o-'.$icon" class="size-3.5" />
                                {{ __('chat.quick.'.$topic) }}
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif

            @foreach ($messages as $i => $m)
                @php
                    $prev = $messages[$i - 1] ?? null;
                    $grouped = $prev && $prev->sender === $m->sender && $prev->user_id === $m->user_id && $m->created_at->diffInMinutes($prev->created_at, true) < 5;
                    $time = $m->created_at->timezone(config('app.timezone'))->format('H:i');
                @endphp
                @if ($m->sender === 'guest')
                    <div wire:key="cm-{{ $m->id }}" class="flex justify-end {{ $grouped ? 'mt-1!' : '' }}">
                        <div class="max-w-[82%] text-right">
                            <div class="inline-block rounded-2xl rounded-br-md bg-gold-500 px-3.5 py-2.5 text-left text-sm leading-relaxed whitespace-pre-line break-words text-white shadow-sm shadow-gold-500/20 dark:bg-gold-600">{{ $m->body }}</div>
                            <p class="mt-1 text-[10px] text-muted">{{ $time }}</p>
                        </div>
                    </div>
                @else
                    @php $isStaff = $m->sender === 'staff'; @endphp
                    <div wire:key="cm-{{ $m->id }}" class="flex items-end gap-2 {{ $grouped ? 'mt-1!' : '' }}"
                         @if ($typingId === $m->id) x-data="{ shown: false }" x-init="setTimeout(() => { shown = true; scroll(true) }, 900)" @endif>
                        @if ($grouped)
                            <div class="w-7 shrink-0"></div>
                        @elseif ($isStaff)
                            <div class="grid size-7 shrink-0 place-items-center rounded-full bg-gold-100 text-[11px] font-bold text-gold-800 dark:bg-gold-800 dark:text-gold-100">{{ mb_strtoupper(mb_substr($m->user?->name ?? 'A', 0, 1)) }}</div>
                        @else
                            <div class="grid size-7 shrink-0 place-items-center rounded-full bg-midnight-900 font-serif text-sm font-semibold text-gold-300 dark:bg-gold-900/70">A</div>
                        @endif
                        <div class="max-w-[82%]">
                            @unless ($grouped)
                                <p class="mb-1 text-[11px] font-semibold text-muted">
                                    @if ($isStaff)
                                        {{ $m->user ? \Illuminate\Support\Str::before($m->user->name, ' ') : '' }} <span class="font-normal">· {{ __('chat.widget.staff') }}</span>
                                    @else
                                        {{ __('chat.widget.bot') }}
                                    @endif
                                </p>
                            @endunless
                            @if ($typingId === $m->id)
                                <div x-show="! shown" class="inline-flex items-center gap-1 rounded-2xl rounded-bl-md border border-line bg-surface px-4 py-3.5 shadow-sm" aria-label="{{ __('chat.widget.typing') }}">
                                    <span class="size-1.5 animate-bounce rounded-full bg-muted [animation-delay:-0.3s]"></span>
                                    <span class="size-1.5 animate-bounce rounded-full bg-muted [animation-delay:-0.15s]"></span>
                                    <span class="size-1.5 animate-bounce rounded-full bg-muted"></span>
                                </div>
                            @endif
                            <div @if ($typingId === $m->id) x-show="shown" x-cloak @endif>
                                <div class="rounded-2xl rounded-bl-md border px-3.5 py-2.5 text-sm leading-relaxed break-words text-ink shadow-sm [&_a]:font-semibold [&_a]:text-gold-700 [&_a]:underline [&_a]:decoration-gold-400/60 [&_a]:underline-offset-2 hover:[&_a]:decoration-gold-500 dark:[&_a]:text-gold-300 {{ $isStaff ? 'border-gold-300/60 bg-gold-50 dark:border-gold-700/50 dark:bg-gold-900/25' : 'border-line bg-surface' }}">{!! $m->html !!}</div>
                                <p class="mt-1 text-[10px] text-muted">{{ $time }}</p>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach

            @if ($conversation && $conversation->status === 'closed')
                <p class="mx-auto max-w-[90%] rounded-xl bg-elevated px-3 py-2 text-center text-xs text-muted">{{ __('chat.widget.closed_note') }}</p>
            @endif
        </div>

        {{-- Composer --}}
        <form wire:submit="send" class="border-t border-line bg-surface px-3 pt-3 pb-[max(0.75rem,env(safe-area-inset-bottom))]">
            @if ($showIntro)
                <p class="mb-2 px-1 text-[11px] text-muted">{{ __('chat.widget.intro_hint') }}</p>
                <div class="mb-2 grid grid-cols-2 gap-2">
                    <label class="sr-only" for="chat-name">{{ __('chat.widget.name') }}</label>
                    <input id="chat-name" type="text" wire:model="name" maxlength="80" autocomplete="name" placeholder="{{ __('chat.widget.name') }}"
                           class="min-w-0 rounded-xl border border-line bg-page px-3 py-2 text-sm text-ink placeholder:text-muted/80 focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none">
                    <label class="sr-only" for="chat-email">{{ __('chat.widget.email') }}</label>
                    <input id="chat-email" type="email" wire:model="email" maxlength="150" autocomplete="email" placeholder="{{ __('chat.widget.email') }}"
                           class="min-w-0 rounded-xl border border-line bg-page px-3 py-2 text-sm text-ink placeholder:text-muted/80 focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none">
                </div>
            @endif
            <div class="flex items-end gap-2">
                <label class="sr-only" for="chat-input">{{ __('chat.widget.placeholder') }}</label>
                <textarea id="chat-input" x-ref="input" wire:model="body" rows="1" maxlength="{{ \App\Models\ChatMessage::MAX_LENGTH }}"
                          placeholder="{{ __('chat.widget.placeholder') }}"
                          @input="grow($el)"
                          @keydown.enter="if (! $event.shiftKey && ! $event.isComposing) { $event.preventDefault(); $wire.send().then(() => grow($el)) }"
                          class="max-h-32 min-h-11 flex-1 resize-none rounded-2xl border bg-page px-4 py-2.5 text-sm leading-relaxed text-ink placeholder:text-muted/80 focus:border-gold-500 focus:ring-2 focus:ring-gold-500/20 focus:outline-none {{ $errors->has('body') ? 'border-rose-400' : 'border-line' }}"
                          @error('body') aria-invalid="true" aria-describedby="chat-error" @enderror></textarea>
                <button type="submit" wire:loading.attr="disabled" wire:target="send,ask"
                        class="grid size-11 shrink-0 place-items-center rounded-full bg-gold-500 text-white shadow-lg shadow-gold-500/25 transition hover:bg-gold-600 disabled:opacity-60"
                        aria-label="{{ __('chat.widget.send') }}">
                    <x-heroicon-s-paper-airplane wire:loading.remove wire:target="send,ask" class="size-5" />
                    <span wire:loading wire:target="send,ask" class="size-4 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                </button>
            </div>
            @error('body')
                <p id="chat-error" class="mt-1.5 px-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>
            @else
                <p class="mt-1.5 hidden px-1 text-[10px] text-muted sm:block">{{ __('chat.widget.enter_hint') }}</p>
            @enderror
        </form>
    </section>
</div>
