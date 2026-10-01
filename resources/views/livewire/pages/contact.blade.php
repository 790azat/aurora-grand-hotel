<div>
    <x-page-hero :eyebrow="__('site.contact.eyebrow')" :title="__('site.contact.title')" :subtitle="__('site.contact.subtitle')" size="sm"
                 image="https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=2000&q=80" />

    <section class="section">
        <div class="container-x grid gap-12 lg:grid-cols-[1fr_1.3fr] lg:gap-16">
            {{-- Details --}}
            <div class="space-y-6">
                <div>
                    <h2 class="font-serif text-3xl">{{ __('site.contact.details_title') }}</h2>
                    <p class="mt-2 text-muted">{{ __('site.contact.details_text') }}</p>
                </div>
                <ul class="space-y-3">
                    @foreach ([
                        ['icon' => 'map-pin', 'label' => __('site.contact.address'), 'value' => \App\Models\Setting::localized('hotel_address'), 'href' => null],
                        ['icon' => 'phone', 'label' => __('site.contact.phone'), 'value' => setting('hotel_phone'), 'href' => 'tel:'.preg_replace('/[^\d+]/', '', setting('hotel_phone'))],
                        ['icon' => 'envelope', 'label' => 'Email', 'value' => setting('hotel_email'), 'href' => 'mailto:'.setting('hotel_email')],
                        ['icon' => 'chat-bubble-oval-left-ellipsis', 'label' => 'WhatsApp', 'value' => '+'.setting('whatsapp'), 'href' => 'https://wa.me/'.setting('whatsapp')],
                    ] as $item)
                        <li class="card flex items-center gap-4 p-4">
                            <span class="grid size-11 shrink-0 place-items-center rounded-full bg-gold-100 text-gold-700 dark:bg-gold-900/50 dark:text-gold-200"><x-hotel-icon :name="$item['icon']" class="size-5" /></span>
                            <div class="min-w-0">
                                <p class="text-xs font-semibold uppercase tracking-wider text-muted">{{ $item['label'] }}</p>
                                @if ($item['href'])
                                    <a href="{{ $item['href'] }}" class="break-words font-medium hover:text-gold-600 dark:hover:text-gold-300" @if (str_starts_with($item['href'], 'http')) target="_blank" rel="noopener" @endif>{{ $item['value'] }}</a>
                                @else
                                    <p class="font-medium">{{ $item['value'] }}</p>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>

                <div class="card p-6">
                    <h3 class="flex items-center gap-2 font-serif text-2xl"><x-heroicon-o-clock class="size-6 text-gold-500" /> {{ __('site.contact.hours_title') }}</h3>
                    <dl class="mt-4 divide-y divide-line text-sm">
                        @foreach (__('site.contact.hours') as $row)
                            <div class="flex justify-between gap-4 py-2.5">
                                <dt class="text-muted">{{ $row['name'] }}</dt>
                                <dd class="text-right font-medium">{{ str_replace([':in', ':out'], [setting('check_in_time'), setting('check_out_time')], $row['time']) }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </div>
            </div>

            {{-- Form --}}
            <div class="card p-6 sm:p-10">
                @if ($sent)
                    <div class="mb-8 flex items-start gap-3 rounded-2xl bg-emerald-50 p-4 text-sm text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300" role="status">
                        <x-heroicon-o-check-circle class="size-6 shrink-0" />
                        <div>
                            <p class="font-semibold">{{ __('site.contact.success_title') }}</p>
                            <p class="mt-0.5">{{ __('site.contact.success') }}</p>
                        </div>
                    </div>
                @endif
                <h2 class="font-serif text-3xl">{{ __('site.contact.form_title') }}</h2>
                <p class="mt-2 text-sm text-muted">{{ __('site.contact.form_text') }}</p>

                <form wire:submit="send" class="mt-8 grid gap-5 sm:grid-cols-2" novalidate>
                    <div>
                        <label for="c-name" class="label">{{ __('site.contact.name') }} *</label>
                        <input id="c-name" type="text" wire:model.blur="name" autocomplete="name" class="input @error('name') border-rose-400 @enderror" @error('name') aria-invalid="true" aria-describedby="c-name-err" @enderror>
                        @error('name')<p id="c-name-err" class="input-error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="c-email" class="label">Email *</label>
                        <input id="c-email" type="email" wire:model.blur="email" autocomplete="email" class="input @error('email') border-rose-400 @enderror" @error('email') aria-invalid="true" aria-describedby="c-email-err" @enderror>
                        @error('email')<p id="c-email-err" class="input-error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="c-phone" class="label">{{ __('site.contact.phone') }}</label>
                        <input id="c-phone" type="tel" wire:model.blur="phone" autocomplete="tel" placeholder="+1 555 000 0000" class="input @error('phone') border-rose-400 @enderror">
                        @error('phone')<p class="input-error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="c-subject" class="label">{{ __('site.contact.subject') }} *</label>
                        <select id="c-subject" wire:model="subject" class="input">
                            @foreach (\App\Livewire\Pages\Contact::SUBJECTS as $s)
                                <option value="{{ $s }}">{{ __('site.contact.subjects.'.$s) }}</option>
                            @endforeach
                        </select>
                        @error('subject')<p class="input-error">{{ $message }}</p>@enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label for="c-message" class="label">{{ __('site.contact.message') }} *</label>
                        <textarea id="c-message" wire:model.blur="message" rows="6" class="input resize-y @error('message') border-rose-400 @enderror" placeholder="{{ __('site.contact.message_placeholder') }}"></textarea>
                        @error('message')<p class="input-error">{{ $message }}</p>@enderror
                    </div>
                    <div class="flex flex-col gap-4 sm:col-span-2 sm:flex-row sm:items-center sm:justify-between">
                        <p class="text-xs text-muted">{!! __('site.contact.privacy_note', ['link' => '<a href="'.route('privacy').'" class="link">'.e(__('site.footer.privacy')).'</a>']) !!}</p>
                        <button type="submit" class="btn-gold shrink-0" wire:loading.attr="disabled" wire:target="send">
                            <span wire:loading.remove wire:target="send" class="flex items-center gap-2"><x-heroicon-o-paper-airplane class="size-4" /> {{ __('site.contact.send') }}</span>
                            <span wire:loading.flex wire:target="send" class="items-center gap-2"><span class="inline-block size-4 animate-spin rounded-full border-2 border-white border-t-transparent align-middle"></span> {{ __('site.contact.sending') }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="container-x mt-16">
            <x-map-embed class="h-[420px]" />
        </div>
    </section>
</div>
