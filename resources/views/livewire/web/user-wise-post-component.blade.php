<div>
    <!-- Selected User Profile -->
    <section class="px-4 pt-6 max-w-7xl mx-auto">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <div class="relative w-16 h-16 md:w-20 md:h-20 rounded-full overflow-hidden ring-2 ring-indigo-500/30">
                    <img src="{{ $user->getFirstMediaUrl('profile', 'thumb') }}" onerror="{{ getErrorProfile($user) }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
                </div>
                <div>
                    <h1 class="text-xl md:text-2xl font-bold">{{ $user->name }}</h1>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        <span>{{ $posts->total() }} @lang('posts')</span>
                        <span class="mx-2">•</span>
                        <span>@lang('Joined') {{ optional($user->created_at)->format('M Y') }}</span>
                    </div>
                </div>
            </div>
            @if($user->email)
                <a wire:navigate href="mailto:{{ $user->email }}" class="hidden sm:inline-flex items-center text-xs px-3 py-1 rounded-full bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-300 hover:bg-indigo-100 dark:hover:bg-indigo-500/20">
                    <i class='bx bx-envelope mr-1'></i> {{ $user->email }}
                </a>
            @endif
        </div>
    </section>

    <!-- Posts Grid styled like Latest Posts -->
    <section class="px-4 py-6 max-w-7xl mx-auto">
        <div class="grid gap-6 sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-3">
            @forelse($posts as $post)
                <article class="group bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden flex flex-col">
                    <div class="relative h-44 overflow-hidden">
                        <img src="{{ $post->image}}" onerror="{{ getErrorImage() }}" alt="{{ $post->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
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
                <p class="text-sm text-gray-500 dark:text-gray-400">@lang('No posts found for this user.')</p>
            @endforelse
        </div>

        <div class="mt-6">
            {{ $posts->links() }}
        </div>
    </section>

    <!-- All Users Bottom -->
    <section class="px-4 pt-2 pb-12 max-w-7xl mx-auto" aria-label="All Users">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-sm font-semibold tracking-wide uppercase text-gray-500 dark:text-gray-400">@lang('All Users')</h2>
            <span class="text-xs text-gray-400">{{ count($allUsers) }} @lang('users')</span>
        </div>
        <div class="grid sm:grid-cols-2 md:grid-cols-3 gap-3">
            @forelse($allUsers as $u)
                <a wire:navigate href="{{ route('web.user.wise.post', $u) }}" class="flex items-center rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-3 hover:border-indigo-300 dark:hover:border-indigo-500 transition-colors {{ $u->id === $user->id ? 'ring-1 ring-indigo-500/50' : '' }}">
                    <img src="{{ getUserProfileImage($u) }}" onerror="{{ getErrorProfile($u) }}" alt="{{ $u->name }}" class="w-9 h-9 rounded-full object-cover" />
                    <div class="ml-3">
                        <p class="text-sm font-medium {{ $u->id === $user->id ? 'text-indigo-600 dark:text-indigo-400' : '' }}">{{ $u->name }}</p>
                        <p class="text-[11px] text-gray-500 dark:text-gray-400">{{ $u->posts_count }} @lang('posts')</p>
                    </div>
                </a>
            @empty
                <p class="text-xs text-gray-400">@lang('No users found.')</p>
            @endforelse
        </div>
    </section>
</div>
