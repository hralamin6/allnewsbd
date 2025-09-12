@section('title', $post->title)
@section('description', Str::limit(strip_tags($post->content), 333))
@section('image', $post->getFirstMediaUrl('postImages', 'avatar'))
<div>
    <!-- Breadcrumb -->
    <nav class="px-4 pt-6 max-w-5xl mx-auto text-xs text-gray-500 dark:text-gray-400" aria-label="Breadcrumb">
        <ol class="flex items-center gap-2">
            <li><a wire:navigate href="{{ route('web.home') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">@lang('Home')</a></li>
            <li class="opacity-60">/</li>
            <li><a wire:navigate href="{{ route('web.category.wise.post', $post->category_id) }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">{{ $post->category?->parent?->name .' | '. $post->category?->name ?? __('General') }}</a></li>
            <li class="opacity-60">/</li>
            <li class="truncate max-w-[60vw] md:max-w-none" title="{{ $post->title }}">{{ $post->title }}</li>
        </ol>
    </nav>

    @php
        $hero = $post->getFirstMediaUrl('postImages', 'avatar');
        $words = str_word_count(strip_tags($post->content ?? ''));
        $shareUrl = urlencode(request()->fullUrl());
        $shareText = urlencode($post->title);
                $tags = $post->tags()->get();

    @endphp

    <!-- Title + Meta -->
    <header class="px-4 mt-3 max-w-5xl mx-auto">
        <h1 class="text-3xl md:text-5xl font-extrabold leading-tight tracking-tight text-gray-900 dark:text-white">{{ $post->title }}</h1>

        <div class="mt-5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white/70 dark:bg-gray-800/60 backdrop-blur-sm">
            <div class="px-3 sm:px-4 py-3 flex items-center gap-3 sm:gap-4 flex-wrap">
                <!-- Author -->
                <a wire:navigate href="{{ route('web.user.wise.post', $post->user_id) }}" class="flex items-center gap-2 hover:text-indigo-600 dark:hover:text-indigo-400">
                    <img src="{{ getUserProfileImage($post->user) }}" onerror="{{ getErrorProfile($post->user) }}" class="w-9 h-9 rounded-full object-cover ring-1 ring-gray-200 dark:ring-gray-700" alt="author" />
                    <span class="font-medium">{{ $post->user->name ?? 'N/A' }}</span>
                </a>

                <span class="hidden sm:inline w-px h-5 bg-gray-200 dark:bg-gray-700"></span>

                <!-- Date -->
                <span class="inline-flex items-center gap-1.5 text-gray-600 dark:text-gray-400">
                    <svg class="w-4 h-4 opacity-70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M6 21h12a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2z"/>
                    </svg>
                    {{ optional($post->published_at ?? $post->updated_at)->format('M d, Y') }}
                </span>

                <!-- Views -->
                <span class="inline-flex items-center gap-1.5 text-gray-600 dark:text-gray-400">
                    <svg class="w-4 h-4 opacity-70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                    {{ $post->views}}
                </span>

                <!-- Category -->
                <a wire:navigate href="{{ route('web.category.wise.post', $post->category_id) }}" class="inline-flex items-center gap-1.5 px-2 py-0.5 text-[11px] rounded-full bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-300 ring-1 ring-indigo-200/70 dark:ring-indigo-500/20">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M20.59 13.41 11 3.83 7.76 7.07l9.59 9.59 3.24-3.25zM6.34 8.49 3.1 11.73c-.59.59-.59 1.54 0 2.12l7.05 7.05c.59.59 1.54.59 2.12 0l3.24-3.24L6.34 8.49z"/>
                    </svg>
                    {{ $post->category?->parent?->name .' | '. $post->category?->name ?? __('General') }}
                </a>

                <!-- Actions -->
                <div class="ml-auto flex items-center gap-2">
                    <!-- Like button -->
                    <button wire:click="toggleLike" wire:loading.attr="disabled" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-gray-200 dark:border-gray-700 text-xs hover:bg-gray-50 dark:hover:bg-gray-800">
                        @if($liked)
                            <svg class="w-4 h-4 text-rose-600" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M11.645 20.91l-.007-.003-.022-.01a15.247 15.247 0 01-.383-.173 25.18 25.18 0 01-4.244-2.637C4.688 16.223 2.25 13.88 2.25 10.5 2.25 8.015 4.235 6 6.75 6c1.63 0 3.055.792 3.985 2.016a5.01 5.01 0 013.515-2.01l.221-.006H14.25c2.485 0 4.5 1.985 4.5 4.5 0 3.38-2.438 5.723-4.739 7.587a25.114 25.114 0 01-4.244 2.637 15.247 15.247 0 01-.383.173l-.022.01-.007.003-.003.001a.75.75 0 01-.566 0l-.003-.001z"/></svg>
                        @else
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.063-4.5-4.583-4.5-1.699 0-3.174.952-3.917 2.343-.743-1.39-2.218-2.343-3.917-2.343C6.063 3.75 4 5.765 4 8.25c0 3.38 2.438 5.723 4.739 7.587a25.114 25.114 0 004.244 2.637c.132.06.263.118.394.173.131-.055.262-.114.394-.173a25.114 25.114 0 004.244-2.637C18.563 13.973 21 11.63 21 8.25z"/></svg>
                        @endif
                        <span>{{ $likesCount }}</span>
                    </button>

                    <!-- Copy link -->
                    <button x-data="{copyText: 'Copy link',
                            copy(){
                                if(navigator.clipboard && navigator.clipboard.writeText){
                                    navigator.clipboard.writeText(window.location.href).then(()=>this.copyText='Copied').catch(()=>alert('Copy failed!'));
                                }else{
                                    const t=document.createElement('textarea');
                                    t.value=window.location.href; t.style.position='fixed'; document.body.appendChild(t); t.select();
                                    try{ document.execCommand('copy'); this.copyText='Copied'; }catch(e){ alert('Copy failed!'); }
                                    document.body.removeChild(t);
                                }
                            }}"
                            @click="copy()"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-gray-200 dark:border-gray-700 text-xs hover:bg-gray-50 dark:hover:bg-gray-800"
                            x-text="copyText"
                            aria-label="Copy link">
                        @lang('Copy link')
                    </button>

                    <!-- Share dropdown -->
                    <div x-data="{open:false}" class="relative">
                        <button @click="open=!open" @keydown.escape.window="open=false" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-gray-200 dark:border-gray-700 text-xs hover:bg-gray-50 dark:hover:bg-gray-800" aria-haspopup="true" :aria-expanded="open ? 'true' : 'false'">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 8a3 3 0 1 0-6 0 3 3 0 0 0 6 0zM18 20a3 3 0 1 0 0-6 3 3 0 0 0 0 6zM6 20a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"/></svg>
                            <span>Share</span>
                        </button>
                        <div x-cloak="" x-show="open" x-transition.opacity @click.outside="open=false" class="absolute right-0 mt-2 w-60 p-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-lg z-20">
                            <div class="grid grid-cols-2 gap-2 text-xs">
                                <a href="https://www.facebook.com/sharer/sharer.php?u={{ $shareUrl }}" target="_blank" rel="noopener" class="px-2 py-2 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-900/20 text-blue-600 dark:text-blue-400 inline-flex items-center gap-2">
                                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span> Facebook
                                </a>
                                <a href="https://x.com/intent/tweet?url={{ $shareUrl }}&text={{ $shareText }}" target="_blank" rel="noopener" class="px-2 py-2 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 inline-flex items-center gap-2">
                                    <span class="w-1.5 h-1.5 rounded-full bg-gray-500"></span> X
                                </a>
                                <a href="https://wa.me/?text={{ $shareText }}%20{{ $shareUrl }}" target="_blank" rel="noopener" class="px-2 py-2 rounded-lg hover:bg-emerald-50 dark:hover:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 inline-flex items-center gap-2">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> WhatsApp
                                </a>
                                <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ $shareUrl }}" target="_blank" rel="noopener" class="px-2 py-2 rounded-lg hover:bg-sky-50 dark:hover:bg-sky-900/20 text-sky-600 dark:text-sky-400 inline-flex items-center gap-2">
                                    <span class="w-1.5 h-1.5 rounded-full bg-sky-500"></span> LinkedIn
                                </a>
                                <a href="https://www.reddit.com/submit?url={{ $shareUrl }}&title={{ $shareText }}" target="_blank" rel="noopener" class="px-2 py-2 rounded-lg hover:bg-orange-50 dark:hover:bg-orange-900/20 text-orange-600 dark:text-orange-400 inline-flex items-center gap-2">
                                    <span class="w-1.5 h-1.5 rounded-full bg-orange-500"></span> Reddit
                                </a>
                                <a href="https://t.me/share/url?url={{ $shareUrl }}&text={{ $shareText }}" target="_blank" rel="noopener" class="px-2 py-2 rounded-lg hover:bg-cyan-50 dark:hover:bg-cyan-900/20 text-cyan-600 dark:text-cyan-400 inline-flex items-center gap-2">
                                    <span class="w-1.5 h-1.5 rounded-full bg-cyan-500"></span> Telegram
                                </a>
                                <a href="mailto:?subject={{ $shareText }}&body={{ $shareText }}%0A%0A{{ $shareUrl }}" class="px-2 py-2 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 inline-flex items-center gap-2">
                                    <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span> Email
                                </a>
                                <a href="https://www.pinterest.com/pin/create/button/?url={{ $shareUrl }}&description={{ $shareText }}" target="_blank" rel="noopener" class="px-2 py-2 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 text-red-600 dark:text-red-400 inline-flex items-center gap-2">
                                    <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Pinterest
                                </a>
                                <!-- Remove the YouTube and Instagram <a> items and add this button in the same grid -->
                                <button type="button"
                                        @click="
            const title = decodeURIComponent('{{ $shareText }}');
            const url = decodeURIComponent('{{ $shareUrl }}');
            if (navigator.share) {
                navigator.share({ title, text: title, url }).catch(()=>{});
            } else {
                alert('Sharing not supported on this browser.');
            }
        "
                                        class="px-2 py-2 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 inline-flex items-center gap-2 col-span-2 sm:col-span-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-gray-500"></span> Device share
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>


    <!-- Cover Image (optional) -->
{{--    @if($hero)--}}
        <section class="px-4 mt-4 max-w-4xl mx-auto">
            <div class="rounded-2xl overflow-hidden ring-1 ring-gray-200 dark:ring-gray-700 bg-gray-900/5">
                <img src="{{ $hero }}" onerror="{{ getErrorImage() }}" alt="{{ $post->title }}" class="w-full h-64 md:h-[420px] object-cover" />
            </div>
        </section>
{{--    @endif--}}

    <!-- Content -->
    <article class="px-2 mt-4 max-w-4xl mx-auto">
        <div class="rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 py-5 px-3 md:p-7">
            <div class="prose md:prose lg:prose-lg dark:prose-invert max-w-none">
                {!! $post->content !!}
            </div>

            @if($tags->count())
                <div class="flex flex-wrap gap-2 mt-4">
                    @foreach($tags as $tag)
                        @if($tag->slug)
                            <a wire:navigate href="{{route('web.tag.wise.post', $tag->id)}}" class="px-3 py-1 text-xs rounded-full bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-300 hover:bg-indigo-100 dark:hover:bg-indigo-500/20">#{{ $tag->name }}</a>
                        @endif
                    @endforeach
                </div>
            @endif
        </div>
    </article>
    @livewire('web.post-comments-component', ['post' => $post])

    <!-- Prev / Next -->
    <section class="px-4 mt-6 max-w-4xl mx-auto">
        <div class="rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                @if($prevPost)
                    <a wire:navigate href="{{ route('web.post.details', $prevPost->slug) }}" class="group block">
                        <p class="text-[11px] text-gray-500 mb-1">@lang('Previous')</p>
                        <p class="text-sm font-medium group-hover:text-indigo-600 dark:group-hover:text-indigo-400 line-clamp-2">{{ $prevPost->title }}</p>
                    </a>
                @endif
            </div>
            <div class="md:text-right">
                @if($nextPost)
                    <a wire:navigate href="{{ route('web.post.details', $nextPost->slug) }}" class="group block">
                        <p class="text-[11px] text-gray-500 mb-1">@lang('Next')</p>
                        <p class="text-sm font-medium group-hover:text-indigo-600 dark:group-hover:text-indigo-400 line-clamp-2">{{ $nextPost->title }}</p>
                    </a>
                @endif
            </div>
        </div>
    </section>

    <!-- Related Posts -->
    <section class="px-4 py-10 max-w-6xl mx-auto">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-base font-semibold">@lang('Related Posts')</h2>
            <a wire:navigate href="{{ route('web.category.wise.post', $post->category_id) }}" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline">@lang('More in') {{ $post->category?->parent?->name .' | '. $post->category?->name ?? __('General') }}</a>
        </div>
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @forelse($relatedPosts as $rp)
                @php $img = $rp->getFirstMediaUrl('postImages', 'avatar'); @endphp
                <article class="group bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden flex flex-col">
                    <div class="relative h-40 overflow-hidden">
                        <img src="{{ $img }}" onerror="{{ getErrorImage() }}" alt="{{ $rp->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
                        <span class="absolute top-3 left-3 bg-indigo-600 text-white text-[11px] font-medium px-2 py-0.5 rounded-full">{{ $rp->category?->parent?->name .' | '. $rp->category?->name ?? 'General' }}</span>
                        <a wire:navigate href="{{ route('web.post.details', $rp->slug) }}" class="absolute inset-0" aria-label="{{ $rp->title }}"></a>
                    </div>
                    <div class="p-4 flex flex-col flex-1">
                        <a wire:navigate href="{{ route('web.post.details', $rp->slug) }}">
                            <h3 class="font-semibold text-sm leading-snug mb-1 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">{{ $rp->title }}</h3>
                        </a>
                        <p class="text-[12px] text-gray-500 dark:text-gray-400 mb-3">{{ \Illuminate\Support\Str::limit(strip_tags($rp->excerpt ?? $rp->content), 100) }}</p>
                        <div class="mt-auto text-[11px] text-gray-500 dark:text-gray-400">{{ optional($rp->published_at ?? $rp->updated_at)->format('M d, Y') }}</div>
                    </div>
                </article>
            @empty
                <p class="text-sm text-gray-500 dark:text-gray-400">@lang('No related posts.')</p>
            @endforelse
        </div>
    </section>
    <!-- Related User Posts -->
    <section class="px-4 py-10 max-w-6xl mx-auto">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-base font-semibold">@lang('More from this User')</h2>
            <a wire:navigate href="{{ route('web.user.wise.post', $post->user_id) }}" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline">@lang('View all posts by') {{ $post->user->name ?? 'N/A' }}</a>
        </div>
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @forelse($userPosts as $rp)
                @php $img =  $rp->getFirstMediaUrl('postImages', 'avatar'); @endphp
                <article class="group bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden flex flex-col">
                    <div class="relative h-40 overflow-hidden">
                        <img src="{{ $img }}" onerror="{{ getErrorImage() }}" alt="{{ $rp->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
                        <span class="absolute top-3 left-3 bg-indigo-600 text-white text-[11px] font-medium px-2 py-0.5 rounded-full">{{ $rp->category?->parent?->name .' | '. $rp->category?->name ?? 'General' }}</span>
                        <a wire:navigate href="{{ route('web.post.details', $rp->slug) }}" class="absolute inset-0" aria-label="{{ $rp->title }}"></a>
                    </div>
                    <div class="p-4 flex flex-col flex-1">
                        <a wire:navigate href="{{ route('web.post.details', $rp->slug) }}">
                            <h3 class="font-semibold text-sm leading-snug mb-1 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">{{ $rp->title }}</h3>
                        </a>
                        <p class="text-[12px] text-gray-500 dark:text-gray-400 mb-3">{{ \Illuminate\Support\Str::limit(strip_tags($rp->excerpt ?? $rp->content), 100) }}</p>
                        <div class="mt-auto text-[11px] text-gray-500 dark:text-gray-400">{{ optional($rp->published_at ?? $rp->updated_at)->format('M d, Y') }}</div>
                    </div>
                </article>
            @empty
                <p class="text-sm text-gray-500 dark:text-gray-400">@lang('No user related posts.')</p>
            @endforelse
        </div>

</div>
