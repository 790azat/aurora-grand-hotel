<x-filament-panels::page>
    @php
        $locale = app()->getLocale();
        $flags = ['en' => '🇬🇧', 'ru' => '🇷🇺', 'hy' => '🇦🇲'];
    @endphp
    <div class="ag-chat {{ $current ? 'has-selection' : '' }}" wire:poll.4s="poll"
         x-data="{
            scroll() { this.$nextTick(() => { const el = this.$refs.thread; if (el) el.scrollTop = el.scrollHeight }) },
         }">

        {{-- Conversation list --}}
        <aside class="ag-chat-list">
            <div class="ag-chat-list-head">
                <label class="ag-chat-search">
                    <x-filament::icon icon="heroicon-m-magnifying-glass" class="ag-chat-search-icon" />
                    <input type="search" wire:model.live.debounce.300ms="search" placeholder="{{ __('chat.admin.search') }}">
                </label>
                <div class="ag-chat-filters" role="tablist">
                    @foreach (\App\Filament\Pages\LiveChat::FILTERS as $f)
                        <button type="button" role="tab" wire:click="$set('filter', '{{ $f }}')" aria-selected="{{ $filter === $f ? 'true' : 'false' }}"
                                class="ag-chat-filter {{ $filter === $f ? 'is-active' : '' }}">
                            {{ __('chat.admin.filter.'.$f) }}
                            @if (isset($counts[$f]) && $counts[$f])
                                <span class="ag-chat-filter-count {{ $f === 'unread' ? 'is-danger' : '' }}">{{ $counts[$f] }}</span>
                            @endif
                        </button>
                    @endforeach
                </div>
            </div>

            <div class="ag-chat-items">
                @forelse ($conversations as $c)
                    @php
                        $last = $c->latestMessage;
                        $prefix = match ($last?->sender) { 'staff' => __('chat.admin.you').': ', 'bot' => __('chat.admin.bot').': ', default => '' };
                    @endphp
                    <button type="button" wire:key="conv-{{ $c->id }}" wire:click="select({{ $c->id }})"
                            class="ag-chat-item {{ $current?->id === $c->id ? 'is-active' : '' }} {{ $c->unread_count ? 'is-unread' : '' }}">
                        <span class="ag-chat-avatar">{{ mb_strtoupper(mb_substr($c->name ?: '#', 0, 1)) }}</span>
                        <span class="ag-chat-item-body">
                            <span class="ag-chat-item-top">
                                <span class="ag-chat-item-name">{{ $c->display_name }}</span>
                                <span class="ag-chat-locale" title="{{ \App\Http\Middleware\SetLocale::LOCALES[$c->locale] ?? $c->locale }}">{{ $flags[$c->locale] ?? '' }} {{ strtoupper($c->locale) }}</span>
                                <span class="ag-chat-time">{{ $c->last_message_at?->locale($locale)->diffForHumans(short: true) }}</span>
                            </span>
                            <span class="ag-chat-item-bottom">
                                <span class="ag-chat-preview">{{ $prefix }}{{ \Illuminate\Support\Str::limit(preg_replace('/\s+/u', ' ', $last?->body ?? ''), 70) }}</span>
                                @if ($c->unread_count)
                                    <span class="ag-chat-unread">{{ $c->unread_count }}</span>
                                @elseif ($c->status === 'closed')
                                    <x-filament::icon icon="heroicon-m-lock-closed" class="ag-chat-closed-icon" />
                                @endif
                            </span>
                        </span>
                    </button>
                @empty
                    <div class="ag-chat-empty-list">
                        <x-filament::icon icon="heroicon-o-chat-bubble-left-right" class="ag-chat-empty-icon" />
                        {{ __('chat.admin.empty_list') }}
                    </div>
                @endforelse
            </div>
        </aside>

        {{-- Thread --}}
        <section class="ag-chat-thread">
            @if ($current)
                <header class="ag-chat-thread-head">
                    <button type="button" class="ag-chat-back" wire:click="deselect" aria-label="{{ __('chat.admin.back') }}">
                        <x-filament::icon icon="heroicon-m-arrow-left" class="ag-chat-back-icon" />
                    </button>
                    <span class="ag-chat-avatar is-lg">{{ mb_strtoupper(mb_substr($current->name ?: '#', 0, 1)) }}</span>
                    <div class="ag-chat-thread-title">
                        <div class="ag-chat-thread-name">
                            {{ $current->display_name }}
                            @if ($current->status === 'closed')
                                <x-filament::badge color="gray" size="sm">{{ __('chat.admin.closed') }}</x-filament::badge>
                            @endif
                        </div>
                        <div class="ag-chat-thread-meta">
                            {{ $current->email ?: __('chat.admin.no_email') }} · {{ $flags[$current->locale] ?? '' }} {{ \App\Http\Middleware\SetLocale::LOCALES[$current->locale] ?? $current->locale }}
                        </div>
                    </div>
                    <x-filament::button size="sm" color="gray" wire:click="toggleStatus"
                        :icon="$current->status === 'open' ? 'heroicon-m-lock-closed' : 'heroicon-m-lock-open'">
                        <span class="ag-chat-btn-label">{{ $current->status === 'open' ? __('chat.admin.close') : __('chat.admin.reopen') }}</span>
                    </x-filament::button>
                </header>

                <div class="ag-chat-messages" x-ref="thread" x-init="scroll(); new MutationObserver(() => scroll()).observe($el, { childList: true, subtree: true })">
                    @php $day = null; @endphp
                    @foreach ($current->messages as $m)
                        @php $d = $m->created_at->locale($locale)->isoFormat('D MMMM'); @endphp
                        @if ($d !== $day)
                            @php $day = $d; @endphp
                            <div class="ag-chat-day"><span>{{ $d }}</span></div>
                        @endif
                        <div wire:key="msg-{{ $m->id }}" class="ag-chat-msg is-{{ $m->sender }}">
                            <div class="ag-chat-msg-label">
                                @if ($m->sender === 'guest')
                                    {{ $current->display_name }}
                                @elseif ($m->sender === 'bot')
                                    <x-filament::icon icon="heroicon-m-sparkles" class="ag-chat-label-icon" /> {{ __('chat.admin.bot') }}
                                @else
                                    {{ $m->user?->name ?? __('chat.admin.staff') }}
                                @endif
                                <span>· {{ $m->created_at->format('H:i') }}</span>
                                @if ($m->sender === 'guest' && $m->read_at === null)
                                    <span class="ag-chat-new-dot"></span>
                                @endif
                            </div>
                            <div class="ag-chat-bubble">{!! $m->toHtml(spa: false) !!}</div>
                        </div>
                    @endforeach
                </div>

                <form wire:submit="send" class="ag-chat-composer"
                      x-data="{ grow(el) { el.style.height = 'auto'; el.style.height = Math.min(el.scrollHeight, 160) + 'px' } }">
                    <textarea wire:model="reply" rows="1" maxlength="{{ \App\Models\ChatMessage::MAX_LENGTH }}"
                              placeholder="{{ __('chat.admin.reply_placeholder') }}" title="{{ __('chat.widget.enter_hint') }}"
                              @input="grow($el)"
                              @keydown.enter="if (! $event.shiftKey && ! $event.isComposing) { $event.preventDefault(); $wire.send().then(() => grow($el)) }"></textarea>
                    <x-filament::button type="submit" icon="heroicon-m-paper-airplane" wire:target="send">{{ __('chat.admin.send') }}</x-filament::button>
                </form>
                @error('reply')<p class="ag-chat-error">{{ $message }}</p>@enderror
            @else
                <div class="ag-chat-placeholder">
                    <div class="ag-chat-placeholder-icon"><x-filament::icon icon="heroicon-o-chat-bubble-left-right" /></div>
                    <h3>{{ __('chat.admin.select') }}</h3>
                    <p>{{ __('chat.admin.select_text') }}</p>
                </div>
            @endif
        </section>

        {{-- Guest info --}}
        @if ($current)
            <aside class="ag-chat-info">
                <h4>{{ __('chat.admin.guest') }}</h4>
                <dl>
                    <div><dt>{{ __('admin.fields.email') }}</dt><dd>{{ $current->email ?: '—' }}</dd></div>
                    <div><dt>{{ __('chat.admin.language') }}</dt><dd>{{ $flags[$current->locale] ?? '' }} {{ \App\Http\Middleware\SetLocale::LOCALES[$current->locale] ?? $current->locale }}</dd></div>
                    <div><dt>{{ __('chat.admin.started') }}</dt><dd>{{ $current->created_at->locale($locale)->isoFormat('D MMM, HH:mm') }}</dd></div>
                    <div><dt>{{ __('chat.admin.messages') }}</dt><dd>{{ $current->messages->count() }}</dd></div>
                </dl>
                @if ($guestUrl)
                    <x-filament::button tag="a" :href="$guestUrl" size="sm" color="gray" icon="heroicon-m-user" class="ag-chat-info-btn">{{ __('chat.admin.guest_profile') }}</x-filament::button>
                @endif

                <h4>{{ __('chat.admin.bookings') }}</h4>
                @forelse ($bookings as $b)
                    <a href="{{ $b['url'] }}" class="ag-chat-booking">
                        <span class="ag-chat-booking-ref">{{ $b['ref'] }} <i class="is-{{ $b['status'] }}">{{ __('admin.status.'.$b['status']) }}</i></span>
                        <span class="ag-chat-booking-meta">{{ $b['dates'] }}</span>
                        <span class="ag-chat-booking-meta">{{ $b['room'] }}</span>
                    </a>
                @empty
                    <p class="ag-chat-info-muted">{{ $current->email ? __('chat.admin.no_bookings') : __('chat.admin.no_email') }}</p>
                @endforelse

                <p class="ag-chat-info-hint">
                    <x-filament::icon icon="heroicon-m-sparkles" class="ag-chat-label-icon" />
                    {{ __('chat.admin.bot_hint', ['minutes' => $quietMinutes]) }}
                </p>
            </aside>
        @endif
    </div>
</x-filament-panels::page>
