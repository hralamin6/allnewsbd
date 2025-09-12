<div>


    <!-- Header (Selected Tag) -->
    <section class="px-4 pt-6 max-w-7xl mx-auto">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">@lang('Tag')</p>
                <h1 class="text-2xl md:text-3xl font-bold">#{{ $tag->name }}</h1>
            </div>
            <span class="text-sm text-gray-500 dark:text-gray-400">{{ $posts->total() }} @lang('posts')</span>
        </div>
    </section>

    <!-- Posts Grid styled like Latest Posts -->
    <section class="px-4 py-6 max-w-7xl mx-auto">
        <div class="grid gap-6 sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-3">
            @forelse($posts as $post)
                <article class="group bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden flex flex-col">
                    <div class="relative h-44 overflow-hidden">
                        <img src="{{ $post->image}}" onerror="{{getErrorImage()}}" alt="{{ $post->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
                        <span class="absolute top-3 left-3 bg-indigo-600 text-white text-xs font-medium px-3 py-1 rounded-full">{{ $post->category->name ?? 'General' }}</span>
                        <a wire:navigate href="{{ route('web.post.details', $post->slug) }}" class="absolute inset-0" aria-label="{{ $post->title }}"></a>
                    </div>
                    <div class="p-5 flex flex-col flex-1">
                        <a wire:navigate href="{{ route('web.post.details', $post->slug) }}">
                            <h3 class="font-semibold text-sm leading-snug mb-2 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">{{ $post->title }}</h3>
                        </a>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">{{ \Illuminate\Support\Str::limit(strip_tags($post->excerpt ?? $post->content), 120) }}</p>
                        <div class="mt-auto pt-4 border-t border-gray-100 dark:border-gray-700">
                            <div class="flex items-center justify-between text-[11px] text-gray-500 dark:text-gray-400 mb-2">
                                <span class="font-medium">{{ $post->user->name ?? 'N/A' }}</span>
                                <span>{{ optional($post->published_at ?? $post->updated_at)->format('M d, Y') }}</span>
                            </div>
                            <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                                <span></span>
                                <a wire:navigate href="{{ route('web.post.details', $post->slug) }}" class="flex items-center text-indigo-600 dark:text-indigo-400 hover:underline"><i class='bx bx-right-arrow-alt mr-1'></i>@lang('Read')</a>
                            </div>
                        </div>
                    </div>
                </article>
            @empty
                <p class="text-sm text-gray-500 dark:text-gray-400">@lang('No posts found for this tag.')</p>
            @endforelse
        </div>

        <div class="mt-6">
            {{ $posts->links() }}
        </div>
        <section class="px-4 m-12 pt-6 max-w-7xl bg-white dark:bg-gray-800" aria-label="All Tags">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-sm font-semibold tracking-wide uppercase text-gray-500 dark:text-gray-400">@lang('All Tags')</h2>
                <span class="text-xs text-gray-400">{{ count($allTags) }} @lang('tags')</span>
            </div>
            <div class="flex flex-wrap gap-2">
                @foreach($allTags as $t)
                    <a wire:navigate href="{{ route('web.tag.wise.post', $t) }}"
                       class="px-3 py-1 text-xs rounded-full transition-colors
                   {{ $t->id === $tag->id
                        ? 'bg-indigo-600 text-white hover:bg-indigo-700'
                        : 'bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-300 hover:bg-indigo-100 dark:hover:bg-indigo-500/20' }}">
                        #{{ $t->name }}
                        <span class="ml-1 text-[10px] {{ $t->id === $tag->id ? 'text-white/90' : 'text-indigo-500 dark:text-indigo-300' }}">({{ $t->posts_count }})</span>
                    </a>
                @endforeach
            </div>
        </section>
    </section>
    <!-- All Tags Top Section -->

</div>
