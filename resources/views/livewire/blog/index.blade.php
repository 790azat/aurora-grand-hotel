<div>
    <x-page-hero :eyebrow="__('site.blog.eyebrow')" :title="__('site.blog.title')" :subtitle="__('site.blog.subtitle')" size="sm"
                 image="https://images.unsplash.com/photo-1414235077428-338989a2e8c0?auto=format&fit=crop&w=2000&q=80" />

    <section id="journal" class="section scroll-mt-20 pt-10">
        <div class="container-x">
            <div class="flex flex-wrap justify-center gap-2" role="tablist" aria-label="{{ __('site.blog.categories_label') }}">
                <button type="button" role="tab" wire:click="setCategory('')" aria-selected="{{ $category === '' ? 'true' : 'false' }}"
                        class="rounded-full border px-5 py-2 text-sm font-medium transition {{ $category === '' ? 'border-midnight-900 bg-midnight-900 text-white dark:border-gold-400 dark:bg-gold-400 dark:text-midnight-950' : 'border-line hover:border-gold-500 hover:text-gold-600' }}">
                    {{ __('site.blog.all') }}
                </button>
                @foreach ($categories as $cat)
                    <button type="button" role="tab" wire:click="setCategory('{{ $cat }}')" aria-selected="{{ $category === $cat ? 'true' : 'false' }}"
                            class="rounded-full border px-5 py-2 text-sm font-medium transition {{ $category === $cat ? 'border-midnight-900 bg-midnight-900 text-white dark:border-gold-400 dark:bg-gold-400 dark:text-midnight-950' : 'border-line hover:border-gold-500 hover:text-gold-600' }}">
                        {{ __('site.blog.categories.'.$cat) }}
                    </button>
                @endforeach
            </div>

            <div wire:loading.delay.class="opacity-50" class="mt-12 transition-opacity">
                @if ($posts->isEmpty())
                    <x-empty-state icon="newspaper" :title="__('site.blog.empty_title')" :text="__('site.blog.empty_text')" />
                @else
                    @php $lead = $posts->onFirstPage() ? $posts->first() : null; @endphp
                    @if ($lead)
                        <article class="group card mb-14 grid overflow-hidden lg:grid-cols-2" wire:key="lead-{{ $lead->id }}">
                            <a href="{{ route('blog.show', $lead) }}" wire:navigate class="relative block aspect-[16/10] overflow-hidden bg-elevated lg:aspect-auto lg:min-h-[420px]">
                                <img src="{{ $lead->image }}" alt="{{ $lead->title }}" class="absolute inset-0 size-full object-cover transition duration-700 group-hover:scale-105">
                            </a>
                            <div class="flex flex-col justify-center p-7 sm:p-12">
                                <div class="flex items-center gap-3 text-xs">
                                    <span class="badge-gold">{{ __('site.blog.categories.'.$lead->category) }}</span>
                                    <time datetime="{{ $lead->published_at->toDateString() }}" class="uppercase tracking-wider text-muted">{{ $lead->published_at->translatedFormat('j F Y') }}</time>
                                </div>
                                <h2 class="mt-5 font-serif text-3xl leading-tight sm:text-4xl">
                                    <a href="{{ route('blog.show', $lead) }}" wire:navigate class="transition hover:text-gold-600 dark:hover:text-gold-300">{{ $lead->title }}</a>
                                </h2>
                                <p class="lead mt-4">{{ $lead->excerpt }}</p>
                                <a href="{{ route('blog.show', $lead) }}" wire:navigate class="btn-dark mt-8 self-start">{{ __('site.blog.read_more') }} <x-heroicon-o-arrow-right class="size-4" /></a>
                            </div>
                        </article>
                    @endif
                    <div class="grid gap-x-8 gap-y-14 md:grid-cols-2 lg:grid-cols-3">
                        @foreach ($posts as $post)
                            @continue($lead && $post->is($lead))
                            <x-post-card :post="$post" wire:key="post-{{ $post->id }}" />
                        @endforeach
                    </div>
                @endif
            </div>
            <div class="mt-14">
                {{ $posts->links('components.pagination', ['scrollTo' => 'journal']) }}
            </div>
        </div>
    </section>
</div>
