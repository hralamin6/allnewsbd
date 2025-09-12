<!-- resources/views/livewire/web/home-component.blade.php -->
<div>
    <!-- Container -->
    <div class="px-2 md:px-6 pt-6 mx-auto max-w-7xl">
        <!-- Hero: Carousel + Most Viewed -->
        <section class="w-full max-w-7xl mx-auto" x-data="{
            active: 0,
            slides: @js(
                $featured_posts->map(function ($post) {
                    return [
                        'id' => $post->id,
                        'title' => $post->title,
                        'slug' => $post->slug,
                        'category' => $post->category?->parent?->name .' | '. $post->category?->name ?? 'General',
                        'img' => $post->getFirstMediaUrl('postImages', 'avatar') ?: getWebErrorImage(),
                    ];
                })->values()->all()
            ),
            interval: null,
            next(){ if(this.slides.length){ this.active = (this.active + 1) % this.slides.length } },
            prev(){ if(this.slides.length){ this.active = (this.active - 1 + this.slides.length) % this.slides.length } },
            start(){ if(this.slides.length > 1){ this.interval = setInterval(()=>this.next(), 6000) } },
            stop(){ if(this.interval) clearInterval(this.interval) }
        }" x-init="start()">
            <div class="grid grid-cols-12 gap-4">
                <!-- Carousel (col-8 on lg) -->
                <div class="col-span-12 md:col-span-8">
                    <div class="relative h-[360px] md:h-[440px] rounded-2xl overflow-hidden group border border-gray-200 dark:border-gray-700 bg-gray-900/20">
                        <div class="absolute inset-0 overflow-hidden">
                            <div class="h-full w-full flex transition-transform duration-700 ease-out" :style="`transform: translateX(-${active * 100}%);`">
                                <template x-for="slide in slides" :key="slide.id">
                                    <div class="w-full flex-shrink-0 relative h-full">
                                        <img :src="slide.img" alt="slide" class="w-full h-full object-cover" onerror="{{ getWebErrorImage() }}">
                                        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>
                                        <div class="absolute bottom-0 p-6 sm:p-8">
                                            <span class="inline-block text-xs tracking-wide uppercase font-semibold bg-indigo-600 text-white px-3 py-1 rounded-full mb-3" x-text="slide.category"></span>
                                            <h2 class="text-2xl sm:text-3xl font-bold text-white max-w-xl leading-snug" x-text="slide.title"></h2>
                                        </div>
                                        <a wire:navigate
                                           class="absolute inset-0 z-10 cursor-pointer"
                                           :href="('{{ route('web.post.details', ':slug') }}').replace(':slug', slide.slug)"
                                           :aria-label="'Open post ' + slide.title"></a>
                                    </div>
                                </template>
                            </div>
                        </div>
                        <!-- Controls -->
                        <div class="absolute inset-0 flex z-20 items-center justify-between px-4 opacity-0 group-hover:opacity-100 transition pointer-events-none">
                            <button @click.stop="prev()" class="pointer-events-auto h-11 w-11 rounded-full bg-white/20 hover:bg-white/40 text-white backdrop-blur flex items-center justify-center"><i class='bx bx-chevron-left text-2xl'></i></button>
                            <button @click.stop="next()" class="pointer-events-auto h-11 w-11 rounded-full bg-white/20 hover:bg-white/40 text-white backdrop-blur flex items-center justify-center"><i class='bx bx-chevron-right text-2xl'></i></button>
                        </div>
                        <!-- Indicators -->
                        <div class="absolute bottom-4 left-1/2 -translate-x-1/2 flex space-x-2">
                            <template x-for="(dot, di) in slides" :key="'dot-'+dot.id">
                                <button @click="active = di" :class="active===di ? 'w-6 bg-indigo-500' : 'w-2 bg-white/40 hover:bg-white/70'" class="h-2 rounded-full transition-all"></button>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Most Viewed (col-4 on lg) -->
                <aside class="col-span-12 lg:col-span-4 space-y-4">
                    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-4 h-full flex flex-col">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-lg font-semibold">@lang('Most Viewed Posts')</h3>
                        </div>
                        <div class="flex-1 overflow-y-auto custom-scroll space-y-4 pr-2">
                            @forelse($most_view_posts as $post)
                                <a wire:navigate href="{{ route('web.post.details', $post->slug) }}" class="flex items-center space-x-3 group">
                                    <div class="w-16 h-16 rounded-xl overflow-hidden flex-shrink-0 ring-1 ring-gray-200 dark:ring-gray-700">
                                        <img src="{{ $post->getFirstMediaUrl('postImages', 'avatar') }}" onerror="{{ getWebErrorImage() }}" alt="thumb" class="w-full h-full object-cover group-hover:scale-105 transition-transform" />
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium line-clamp-2 group-hover:text-indigo-600 dark:group-hover:text-indigo-400">{{ $post->title }}</p>
                                        <div class="text-[11px] text-gray-500 dark:text-gray-400 mt-1 flex items-center space-x-2">
                                            <span>by {{ $post->user->name }}</span>
                                            <span class="w-1 h-1 rounded-full bg-gray-400"></span>
                                            <span>{{ \Carbon\Carbon::parse($post->updated_at)->diffForHumans(null, true, true) ?? 'N/A' }}</span>
                                        </div>
                                    </div>
                                </a>
                            @empty
                                <p class="text-sm text-gray-500 dark:text-gray-400">@lang('No posts found in this category.')</p>
                            @endforelse
                        </div>
                    </div>
                </aside>
            </div>
        </section>

        <!-- Latest Posts -->
        <section class="px-4 pt-8 max-w-7xl mx-auto" aria-label="Latest Posts">
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @forelse($latest_posts as $post)
                    <article class="group bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden flex flex-col">
                        <div class="relative h-44 overflow-hidden">
                            <img src="{{ $post->getFirstMediaUrl('postImages', 'avatar') }}"
                                 onerror="{{ getWebErrorImage() }}"
                                 alt="{{ $post->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
                            <span class="absolute top-3 left-3 bg-indigo-600 text-white text-xs font-medium px-3 py-1 rounded-full">{{ $post->category?->parent?->name .' | '. $post->category?->name ?? 'General' }}</span>
                            <a wire:navigate href="{{ route('web.post.details', $post->slug) }}" class="absolute inset-0" aria-label="{{ $post->title }}"></a>
                        </div>
                        <div class="p-5 flex flex-col flex-1">
                            <a wire:navigate href="{{ route('web.post.details', $post->slug) }}">
                                <h3 class="font-semibold text-sm leading-snug mb-2 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">{{ $post->title }}</h3>
                            </a>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">
                                {{ \Illuminate\Support\Str::limit(strip_tags($post->excerpt ?? $post->content), 120) }}
                            </p>
                            <div class="mt-auto pt-4 border-t border-gray-100 dark:border-gray-700">
                                <div class="flex items-center justify-between text-[11px] text-gray-500 dark:text-gray-400 mb-2">
                                    <span class="font-medium">{{ $post->user->name ?? 'N/A' }}</span>
                                    <span>{{ optional($post->published_at)->format('M d, Y') }}</span>
                                    <span>{{ optional($post->published_at)->diffForHumans() ?? 'N/A' }}</span>
                                </div>
                                <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                                    <span></span>
                                    <a wire:navigate href="{{ route('web.post.details', $post->slug) }}" class="flex items-center text-indigo-600 dark:text-indigo-400 hover:underline">
                                        <i class='bx bx-right-arrow-alt mr-1'></i>@lang('Read')
                                    </a>
                                </div>
                            </div>
                        </div>
                    </article>
                @empty
                    <p class="text-sm text-gray-500 dark:text-gray-400">@lang('No posts found.')</p>
                @endforelse
            </div>
            <div class="mx-auto my-4 px-4 overflow-y-auto">{{ $latest_posts->links() }}</div>
        </section>

        <!-- Categories & Tags -->
        <section class="px-4 pt-12 max-w-7xl mx-auto" aria-label="Categories and Tags">
            <div class="grid gap-8 md:grid-cols-3">
                <!-- Categories List -->
                <div class="md:col-span-1 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-sm font-semibold tracking-wide uppercase text-gray-500 dark:text-gray-400 mb-4">@lang('Categories')</h3>
                    <ul class="space-y-3 text-sm">
                        @forelse($categories as $cat)
                            <li class="flex items-center justify-between">
                                <a wire:navigate href="{{ route('web.category.wise.post', $cat) }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 font-medium">
                                    {{ $cat->name }}
                                </a>
                                <span class="text-xs text-gray-400">{{ $cat->posts_count }}</span>
                            </li>
                        @empty
                            <li class="text-xs text-gray-400">@lang('No categories found.')</li>
                        @endforelse
                    </ul>
                </div>
                <!-- Tag Cloud -->
                <div class="md:col-span-2 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 flex flex-col">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-semibold tracking-wide uppercase text-gray-500 dark:text-gray-400">@lang('Popular Tags')</h3>
                        <a wire:navigate href="{{ route('web.home') }}#tags" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline">@lang('View all')</a>
                    </div>
                    <div class="flex flex-wrap gap-2" id="tags">
                        @forelse($tags as $tag)
                            <a wire:navigate href="{{ route('web.tag.wise.post', $tag) }}" class="px-3 py-1 text-xs rounded-full bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-300 hover:bg-indigo-100 dark:hover:bg-indigo-500/20">
                                {{ $tag->name }}
                                <span class="ml-1 text-[10px] text-indigo-500 dark:text-indigo-300">({{ $tag->posts_count }})</span>
                            </a>
                        @empty
                            <span class="text-xs text-gray-400">@lang('No tags found.')</span>
                        @endforelse
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="py-10 text-center text-xs text-gray-500 dark:text-gray-400">
            You have reached the end of the feed • © 2025 Social Communication UI
        </footer>
    </div>
</div>
