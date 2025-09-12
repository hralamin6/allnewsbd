<div class="md:px-4 md:mx-auto mt-4">


    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700  w-fit">
        <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-700">
            <h1 class="text-lg font-semibold">Create Post</h1>
            <p class="text-xs text-gray-500">Fill in the details below to publish a new post.</p>
        </div>
        <div class="pt-2 flex items-center gap-3">
            <button wire:click="prothomAlo" wire:loading.remove wire:target="prothomAlo" class="inline-flex items-center px-4 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-500">
                Prothom Alo
            </button>
            <button wire:click="jamunaTv" wire:loading.remove wire:target="jamunaTv" class="inline-flex items-center px-4 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-500">
                Jamuna TV
            </button>
            <button wire:click="banglaNews24" wire:loading.remove wire:target="banglaNews24" class="inline-flex items-center px-4 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-500">
                BanglaNews24
            </button>
            <button wire:click="jagoNews" wire:loading.remove wire:target="jagoNews" class="inline-flex items-center px-4 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-500">
                JagoNews
            </button>
            <button wire:click="banglaTribune" wire:loading.remove wire:target="banglaTribune" class="inline-flex items-center px-4 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-500">
                BanglaTribune
            </button>
        </div>
        <form wire:submit.prevent="save" class="p-5 grid grid-cols-1 lg:grid-cols-3 gap-6 w-fit">
            <!-- Main -->
            <div class="lg:col-span-2 space-y-5">
                <!-- Title & Slug -->
                <div>
                    <label class="block text-sm font-medium mb-1">Title <span class="text-rose-500">*</span>
                        <i class="bx bx-diamond-alt text-lg text-pink-500 bx-tada" wire:click="generateTitle"
                           wire:loading.remove wire:target="generateTitle"></i>
                    </label>
                    <input type="text" wire:model.lazy="title" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900" placeholder="Write a compelling title" />
                    @error('title') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Slug <span class="text-rose-500">*</span></label>
                    <input type="text" wire:model="slug" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900" placeholder="auto-generated-from-title" />
                    @error('slug') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Content -->
                <div>
                    <label class="block text-sm font-medium mb-1">Content <span class="text-rose-500">*</span>
                    <i class="bx bx-diamond-alt text-lg text-pink-500 bx-tada" wire:click="generateDescription" wire:loading.remove wire:target="generateDescription"></i>
                    </label>
                    <div wire:ignore>
                        <trix-editor class="formatted-content  border border-gray-500" x-data x-on:trix-change="$dispatch('input', event.target.value)"
                                     wire:model.debounce.1000ms="content" wire:key="uniqueKey2"></trix-editor>
                    </div>
                    @error('content') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror

                </div>

                <!-- Excerpt -->
                <div>
                    <label class="block text-sm font-medium mb-1">Excerpt</label>
                    <textarea rows="3" wire:model.defer="excerpt" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900" placeholder="Short summary shown on listings"></textarea>
                    @error('excerpt') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-5">
                <!-- Category -->
                <div>
                    <label class="block text-sm font-medium mb-1">Category <span class="text-rose-500">*</span>
                        <i class="bx bx-diamond-alt text-lg text-pink-500 bx-tada" wire:click="generateCategory"
                           wire:loading.remove wire:target="generateCategory"></i></label>
                    <select wire:model.defer="category_id" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900">
                        <option value="">-- Select Category --</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                    @error('category_id') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Status -->
                <div>
                    <label class="block text-sm font-medium mb-1">Status <span class="text-rose-500">*</span></label>
                    <select wire:model.defer="status" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900">
                        <option value="draft">Draft</option>
                        <option value="published">Published</option>
                    </select>
                    @error('status') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Type <span class="text-rose-500">*</span></label>
                    <select wire:model.defer="type" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900">
                        <option value="featured">Featured</option>
                        <option value="normal">Normal</option>
                    </select>
                    @error('status') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Published At (when published) -->
                <div>
                    <label class="block text-sm font-medium mb-1">Publish At</label>
                    <input type="datetime-local" wire:model.defer="published_at" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900" />
                    @error('published_at') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                    <p class="text-[11px] text-gray-500 mt-1">If empty and status is Published, current time will be used.</p>
                </div>

                <!-- Tags -->
                <div>
                    <label class="block text-sm font-medium mb-1">Tags</label>
                    <input type="text" wire:model.defer="tags" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900" placeholder="e.g. laravel, livewire, tailwind" />
                    @error('tags') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                    <p class="text-[11px] text-gray-500 mt-1">Comma-separated; saved as JSON array.</p>
                </div>

                <!-- Cover Image -->
                <div>
                    <label class="block text-sm font-medium mb-1">Cover Image
                        <i class="bx bx-diamond-alt text-lg text-pink-500 bx-tada" wire:click="generateImage"
                           wire:loading.remove wire:target="generateImage"></i></label>
                    <input type="text" wire:model.lazy="url_title" class="w-full mb-2 rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900" />
                    <input type="file" wire:model="cover" accept="image/*" class="block w-full text-sm text-gray-600" />
                    @error('cover') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                    @if ($cover)
                        <div class="mt-3 aspect-video rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700">
                            <img src="{{ $cover->temporaryUrl() }}" class="w-full h-full object-cover" alt="cover preview" />
                        </div>
                    @elseif ($cover_url)
                        <div class="mt-3 aspect-video rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700">
                            <img src="{{ $cover_url }}" onerror="{{ getErrorImage() }}" class="w-full h-full object-cover" alt="cover preview" />
                        </div>
                    @elseif ($editingId)
                        <div class="mt-3 aspect-video rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700">
                            <img src="{{ $post->image}}" onerror="{{ getErrorImage() }}" class="w-full h-full object-cover" alt="cover preview" />
                        </div>
                    @endif
                </div>

                <!-- Actions -->
                <div class="pt-2 flex items-center gap-3">
                    <button type="submit" class="inline-flex items-center px-4 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-500">
                        <i class='bx bx-save mr-2'></i> Save Post
                    </button>
                    <button type="button" wire:click="$refresh" class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700">Reset</button>
                    <button type="button" wire:loading.remove wire:click="generateFullPost" class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700">Full post</button>
                </div>
            </div>
        </form>
    </div>


    <!-- Posts List Table -->
    <section class="p-4 max-w-7xl mx-auto">
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                <h2 class="text-base font-semibold">@lang('Posts')</h2>
                <span class="text-xs text-gray-500">{{ $posts->total() ?? 0 }} @lang('total')</span>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-900/40 text-gray-600 dark:text-gray-300">
                        <tr>
                            <th class="px-4 py-3 text-left">@lang('Post')</th>
                            <th class="px-4 py-3 text-left">@lang('Category')</th>
                            <th class="px-4 py-3 text-left">@lang('Status')</th>
                            <th class="px-4 py-3 text-left">@lang('Author')</th>
                            <th class="px-4 py-3 text-left">@lang('Published')</th>
                            <th class="px-4 py-3 text-right">@lang('Actions')</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($posts as $post)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40">
                            <td class="px-4 py-3">
                                <a wire:navigate href="{{route('web.post.details', $post->slug)}}" class="flex items-center gap-3">
                                    <div class="w-14 h-10 rounded-md overflow-hidden ring-1 ring-gray-200 dark:ring-gray-700 flex-shrink-0">
                                        <img src="{{ $post->image}}" onerror="{{ getErrorImage() }}" alt="thumb" class="w-full h-full object-cover" />
                                    </div>
                                    <div class="min-w-0 w-48">
                                        <div class="font-medium text-gray-900 dark:text-gray-100 truncate">{{ $post->title }}</div>
                                        <div class="text-[11px] text-gray-500 truncate">/{{ $post->slug }}</div>
                                    </div>
                                </a>
                            </td>
                            <td class="px-4 py-3">{{ $post->category->name ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded-full text-xs {{ $post->status === 'published' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300' : 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300' }}">{{ ucfirst($post->status) }}</span>
                            </td>
                            <td class="px-4 py-3">{{ $post->user->name ?? 'N/A' }}</td>
                            <td class="px-4 py-3">{{ optional($post->published_at ?? $post->updated_at)->format('M d, Y') }}</td>
                            <td class="px-4 py-3 text-right">
                                <div class="inline-flex items-center gap-2">
                                    <button type="button" class="px-2 py-1 rounded-md border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700" wire:click="edit({{ $post->id }})">
                                        <i class='bx bx-edit text-base'></i>
                                    </button>
                                    <button type="button" class="px-2 py-1 rounded-md border border-rose-300 text-rose-600 hover:bg-rose-50 dark:border-rose-700 dark:hover:bg-rose-900/20" x-data="{}" @click.prevent="if(confirm('Delete this post?')) { $wire.delete({{ $post->id }}); }">
                                        <i class='bx bx-trash text-base'></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-6 text-center text-sm text-gray-500">@lang('No posts found.')</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-5 py-3">
                {{ $posts->links() }}
            </div>
        </div>
    </section>
</div>
