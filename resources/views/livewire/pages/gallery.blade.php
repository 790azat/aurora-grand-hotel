<div x-data="{
        images: @js($images),
        filter: 'all',
        active: null,
        get visible() { return this.filter === 'all' ? this.images : this.images.filter(i => i.category === this.filter) },
        open(id) { this.active = this.visible.findIndex(i => i.id === id) },
        close() { this.active = null },
        next() { this.active = (this.active + 1) % this.visible.length },
        prev() { this.active = (this.active - 1 + this.visible.length) % this.visible.length },
     }"
     @keydown.escape.window="close()"
     @keydown.arrow-right.window="active !== null && next()"
     @keydown.arrow-left.window="active !== null && prev()">
    <x-page-hero :eyebrow="__('site.gallery.eyebrow')" :title="__('site.gallery.title')" :subtitle="__('site.gallery.subtitle')" size="sm"
                 image="https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?auto=format&fit=crop&w=2000&q=80" />

    <section class="section pt-10">
        <div class="container-x">
            <div class="flex flex-wrap justify-center gap-2" role="tablist" aria-label="{{ __('site.gallery.categories_label') }}">
                @foreach (collect(['all'])->merge($categories) as $category)
                    <button type="button" role="tab" @click="filter = '{{ $category }}'" :aria-selected="filter === '{{ $category }}'"
                            :class="filter === '{{ $category }}' ? 'bg-midnight-900 text-white border-midnight-900 dark:bg-gold-400 dark:text-midnight-950 dark:border-gold-400' : 'border-line hover:border-gold-500 hover:text-gold-600'"
                            class="rounded-full border px-5 py-2 text-sm font-medium transition">
                        {{ __('site.gallery.categories.'.$category) }}
                    </button>
                @endforeach
            </div>

            <div class="mt-10 columns-1 gap-4 sm:columns-2 lg:columns-3">
                <template x-for="(image, index) in visible" :key="image.id">
                    <button type="button" @click="open(image.id)" class="group relative mb-4 block w-full break-inside-avoid overflow-hidden rounded-2xl bg-elevated"
                            x-transition:enter="transition duration-300" x-transition:enter-start="opacity-0 scale-95">
                        <img :src="image.url" :alt="image.caption ?? ''" loading="lazy"
                             :class="['aspect-[4/5]', 'aspect-[4/3]', 'aspect-square', 'aspect-[3/4]', 'aspect-[16/10]'][index % 5]"
                             class="w-full object-cover transition duration-700 group-hover:scale-105">
                        <span class="absolute inset-0 bg-midnight-950/0 transition group-hover:bg-midnight-950/30"></span>
                        <span class="absolute inset-x-0 bottom-0 translate-y-2 bg-gradient-to-t from-midnight-950/85 to-transparent p-4 text-left text-sm text-white opacity-0 transition group-hover:translate-y-0 group-hover:opacity-100" x-text="image.caption"></span>
                        <span class="absolute top-3 right-3 grid size-9 place-items-center rounded-full bg-white/85 text-midnight-900 opacity-0 transition group-hover:opacity-100"><x-heroicon-o-magnifying-glass-plus class="size-4" /></span>
                    </button>
                </template>
            </div>
            <p x-show="visible.length === 0" x-cloak class="py-16 text-center text-muted">{{ __('site.gallery.empty') }}</p>
        </div>
    </section>

    <template x-teleport="body">
        <div x-show="active !== null" x-cloak x-transition.opacity class="fixed inset-0 z-[70] flex flex-col items-center justify-center bg-midnight-950/95 p-4" role="dialog" aria-modal="true" @click.self="close()">
            <button type="button" @click="close()" class="absolute top-4 right-4 grid size-12 place-items-center rounded-full text-white/80 hover:bg-white/10 hover:text-white" aria-label="{{ __('site.common.close') }}"><x-heroicon-o-x-mark class="size-7" /></button>
            <button type="button" @click="prev()" class="absolute left-2 grid size-12 place-items-center rounded-full text-white/80 hover:bg-white/10 hover:text-white sm:left-6" aria-label="{{ __('site.common.previous') }}"><x-heroicon-o-chevron-left class="size-8" /></button>
            <template x-if="active !== null && visible[active]">
                <figure class="flex max-w-5xl flex-col items-center">
                    <img :src="visible[active].url" :alt="visible[active].caption ?? ''" class="max-h-[80vh] max-w-full rounded-2xl object-contain shadow-2xl">
                    <figcaption class="mt-4 text-center text-sm text-white/80">
                        <span x-text="visible[active].caption"></span>
                        <span class="ml-2 text-white/50"><span x-text="active + 1"></span> / <span x-text="visible.length"></span></span>
                    </figcaption>
                </figure>
            </template>
            <button type="button" @click="next()" class="absolute right-2 grid size-12 place-items-center rounded-full text-white/80 hover:bg-white/10 hover:text-white sm:right-6" aria-label="{{ __('site.common.next') }}"><x-heroicon-o-chevron-right class="size-8" /></button>
        </div>
    </template>
</div>
