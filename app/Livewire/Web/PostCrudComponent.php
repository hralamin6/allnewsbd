<?php

namespace App\Livewire\Web;

use App\Jobs\ScrapeBanglaTribuneJob;
use App\Jobs\ScrapeJagoNewsJob;
use App\Jobs\ScrapeJamunaTvJob;
use App\Jobs\ScrapeNews24Job;
use App\Jobs\ScrapeProthomAloJob;
use App\Jobs\SendWebPushNotifications;
use App\Models\Category;
use App\Models\Post;
use http\Client;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use League\CommonMark\CommonMarkConverter;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Symfony\Component\DomCrawler\Crawler;

class PostCrudComponent extends Component
{
    use LivewireAlert;
    use WithFileUploads;
    use WithPagination;

    // Form fields
    public $news = [];
    public $title = '';
    public $type = 'featured';
    public $slug = '';
    public $category_id = '';
    public $content = '';
    public $excerpt = '';
    public $tags = '';
    public $status = 'published';
    public $published_at = null;
    public $meta_title = '';
    public $meta_description = '';

    // Uploads
    public $cover;
    public $post;
    public $url_title;
    public $cover_url;
    public $editingId = null; // For editing existing posts

    // Data
    public $categories = [];

    protected function rules(): array
    {
        return [
            'title' => ['required','string','max:255'],
            'slug' => [
                'required','string','max:255',
                Rule::unique('posts','slug')->ignore($this->editingId)
            ],
            'category_id' => ['required','exists:categories,id'],
            'content' => ['required','string'],
            'excerpt' => ['nullable','string'],
            'tags' => ['nullable','string'], // comma separated in UI
            'status' => ['required','in:draft,published'],
            'type' => ['required','in:featured,normal'],
            'published_at' => ['nullable','date'],
            'meta_title' => ['nullable','string','max:255'],
            'meta_description' => ['nullable','string','max:255'],
            'cover_url' => ['nullable'],
            'cover' => ['nullable','image','max:2048'], // 2MB
        ];
    }

    public function updatedUrlTitle()
    {
//        dd('asdf');
        $this->cover_url = 'https://image.pollinations.ai/prompt/'.$this->url_title.'?model=flux-realism&seed=420&width=512&height=512&private=true&enhance=true&safe=true&transparent=true&token=-8Vug_InGSB3XYP-';
    }

    public function mount(): void
    {
        $this->categories = Category::orderBy('name')->get(['id','name']);
        $this->published_at = now()->format('Y-m-d\TH:i'); // Default to current time
    }

    public function updatedTitle(): void
    {
            $this->slug = Str::slug($this->title);
    }

    public function edit(int $postId): void
    {
        $post = Post::findOrFail($postId);
        $this->post = Post::findOrFail($postId);
        $this->editingId = $post->id;
        $this->title = $post->title;
        $this->type = $post->type;
        $this->slug = $post->slug;
        $this->category_id = $post->category_id;
        $this->content = $post->content;
        $this->excerpt = $post->excerpt ?? '';
        $this->tags = $post->tags()->pluck('name')->implode(', ');
        $this->status = $post->status;
        $this->published_at = optional($post->published_at)->format('Y-m-d\TH:i');
        $this->meta_title = $post->meta_title ?? '';
        $this->meta_description = $post->meta_description ?? '';
        $this->cover_url = '';
        $this->cover = null;
    }

    public function delete(int $postId): void
    {
        $post = Post::findOrFail($postId);
        $post->delete();
        $this->alert('success', __('Data deleted successfully'));
        $this->resetPage();
    }

    public function save(): void
    {

        $this->validate();
        $tags = $this->prepareTags($this->tags);
        $this->meta_title = $this->meta_title ?: $this->title;
        $this->meta_description = $this->meta_description ?: $this->excerpt;



        if ($this->editingId) {
            $post = Post::findOrFail($this->editingId);
            $post->update([
                'user_id' => auth()->id(),
                'category_id' => $this->category_id,
                'title' => $this->title,
                'type' => $this->type,
                'slug' => $this->slug,
                'content' => $this->content,
                'excerpt' => $this->excerpt ?: null,
                'status' => $this->status,
                'meta_title' => $this->meta_title ?: null,
                'meta_description' => $this->meta_description ?: null,
                'published_at' => $this->status === 'published' ? ($this->published_at ?: now()) : null,
            ]);
        } else {
            $post = Post::create([
                'user_id' => auth()->id(),
                'category_id' => $this->category_id,
                'title' => $this->title,
                'type' => $this->type,
                'slug' => $this->slug,
                'content' => $this->content,
                'excerpt' => $this->excerpt ?: null,
                'status' => $this->status,
                'meta_title' => $this->meta_title ?: null,
                'meta_description' => $this->meta_description ?: null,
                'published_at' => $this->status === 'published' ? ($this->published_at ?: now()) : null,
            ]);
        }


        $tagIds = [];
        if (!empty($tags)) {
            foreach ($tags as $tag) {
                $tagModel = \App\Models\Tag::firstOrCreate(['name' => $tag, 'slug' => Str::slug($tag)]);
                $tagIds[] = $tagModel->id;
            }
        }
        $post->tags()->sync($tagIds);
        if ($this->cover_url!=null) {
            $extension = pathinfo(parse_url($this->cover_url, PHP_URL_PATH), PATHINFO_EXTENSION);
            $media =  $post->addMediaFromUrl($this->cover_url)->usingFileName($post->id. '.' . $extension)->toMediaCollection('postImages');
            $path = storage_path("app/public/Post/".$media->id.'/'. $media->file_name);
            if (file_exists($path)) {
                unlink($path);
            }

        }elseif($this->cover!=null){
                $media = $post->addMedia($this->cover->getRealPath())->usingFileName($post->id. '.' . $this->cover->extension())->toMediaCollection('postImages');
                $path = storage_path("app/public/Post/".$media->id.'/'. $media->file_name);
                if (file_exists($path)) {
                    unlink($path);
                }
        }
        $this->alert('success', __('Data updated successfully'));
        $payload = json_encode([
            'title' => 'New Post has been published',
            'body' => $this->title,
            'url' => route('web.post.details', $post->slug),
            'icon' => $post->getFirstMediaUrl('postImages', 'avatar')
        ]);
        SendWebPushNotifications::dispatch($payload);
        $this->resetForm();
        // Optionally redirect
        // return redirect()->route('web.post.details', $post->slug);
    }

    private function prepareTags($tags): ?array
    {
        if (!$tags) return null;
        $parts = collect(explode(',', $tags))
            ->map(fn($t) => trim($t))
            ->filter()
            ->values();
        return $parts->isEmpty() ? null : $parts->all();
    }

    private function resetForm(): void
    {
        $this->reset([
            'title','post','url_title', 'editingId', 'slug','category_id','content','excerpt','tags','status','published_at','meta_title','meta_description','cover','cover_url'
        ]);
        $this->status = 'draft';
        $this->categories = Category::orderBy('name')->get(['id','name']);
    }

    public function render()
    {
        $posts = Post::where('user_id', auth()->id())->with(['category','user'])
            ->latest('id')
            ->paginate(10);

        return view('livewire.web.post-crud-component', compact('posts'))
            ->layout('components.layouts.web');
    }



    public function generateCategory($key = 'OPENROUTER_API_KEY')
    {
        $this->validate(['content' => 'required|min:20']);
        $apiKey = env($key);
        if ($apiKey == 'OPENROUTER_API_KEY') {
            $url = 'https://openrouter.ai/api/v1/chat/completions';
            $model = 'openai/gpt-oss-20b:free';
        }else{
            $url = 'https://text.pollinations.ai/openai';
            $model = 'openai';
        }
        $categoriesPayload = $this->categories->map(fn($c) => ['id' => $c->id, 'name' => $c->name])->values()->all();
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
        ])->post($url, [
            'model' => $model,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are a strict JSON classifier. Choose the single best category id for the given content from the provided categories. Respond with JSON ONLY like: {"id": 123}. No extra text.',
                ],
                [
                    'role' => 'user',
                    'content' => json_encode([
                        'categories' => $categoriesPayload,
                        'content' => \Illuminate\Support\Str::limit(strip_tags((string)$this->content), 4000),
                    ]),
                ],
            ],
        ]);

        if ($response->successful()) {
            $generatedText = $response->json('choices.0.message.content');
            $json = [];
            if (preg_match('/\{.*\}/s', (string)$generatedText, $m)) {
                $decoded = json_decode($m[0], true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $json = $decoded;
                }
            }
            $idFromAi = isset($json['id']) ? (int)$json['id'] : null;
            $this->category_id = $idFromAi;
        } else {
            $this->alert('error', 'Failed to generate title.');
        }
    }
    public function generateTitle($key = 'OPENROUTER_API_KEY')
    {
        $this->validate(['content' => 'required|min:20']);
        $apiKey = env($key);
        if ($apiKey == 'OPENROUTER_API_KEY') {
            $url = 'https://openrouter.ai/api/v1/chat/completions';
            $model = 'openai/gpt-oss-20b:free';
        }else{
            $url = 'https://text.pollinations.ai/openai';
            $model = 'openai';
        }
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
        ])->post($url, [
            'model' => $model,
            'messages' => [
                    [
                        'role' => 'user',
                        'content' => "Generate a concise title  10 words) in bangla for the following note description:\n\n" . $this->content
                    ]
                ]
            ]);

        if ($response->successful()) {
            $generatedText = $response->json('choices.0.message.content');
            $this->title = trim($generatedText, " \n\r\t\v\x00\"'"); // Clean up quotes and whitespace
        } else {
            $this->alert('error', 'Failed to generate title.');
        }
    }
    public function generateImage($key = 'OPENROUTER_API_KEY')
    {
        $this->validate(['content' => 'required|min:20']);
        $apiKey = env($key);
        if ($apiKey == 'OPENROUTER_API_KEY') {
            $url = 'https://openrouter.ai/api/v1/chat/completions';
            $model = 'openai/gpt-oss-20b:free';
        }else{
            $url = 'https://text.pollinations.ai/openai';
            $model = 'openai';
        }
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
        ])->post($url, [
            'model' => $model,
            'messages' => [
                ['role' => 'system', 'content' => 'Generate a concise image generation prompt in english language in  for the following description 20 words. The prompt should be suitable for generating an image using AI.'],

                [
                        'role' => 'user',
                        'content' => "\n\n" . $this->content
                    ]
                ]
            ]);

        if ($response->successful()) {
            $generatedText = $response->json('choices.0.message.content');
            $this->url_title = trim($generatedText, " \n\r\t\v\x00\"'"); // Clean up quotes and whitespace
            $this->updatedUrlTitle();
        } else {
            $this->alert('error', 'Failed to generate title.');
        }
    }


    public function generateDescription($key = 'OPENROUTER_API_KEY')
    {
        $this->validate(['title' => 'required|min:5']);
        $apiKey = env($key);
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
        ])->post('https://openrouter.ai/api/v1/chat/completions', [
                'model' => 'openai/gpt-oss-20b:free',
                'messages' => [
                ['role' => 'system', 'content' => 'Generate a nice description for the following title in bangla. The description should be informative and engaging, suitable for a blog post.'],
                    [
                        'role' => 'user', 'content' => $this->title
                    ],
                ],
            ]);
        if ($response->failed()) {
            $this->alert('error', response()->json($response->json(), $response->status()));
            $this->generateDescription('POLLINATIONS_API_TOKEN');
        }
        $converter = new CommonMarkConverter();
        $markdown = $response['choices'][0]['message']['content']; // AI output
        $html = $converter->convert($markdown);
        $this->content = (string) $html;
    }

    protected function pollinationsUrl(string $prompt, array $params = []): string
    {
        $defaults = [
            'width'  => 1280,
            'height' => 720,
            'model'  => 'flux',   // try: flux, sdxl, lcm, etc.
            'seed'   => random_int(1, 10_000_000),
            'nologo' => 'true',
        ];
        $q = http_build_query(array_filter($params + $defaults, fn($v) => $v !== null && $v !== ''));
        return 'https://image.pollinations.ai/prompt/' . urlencode($prompt) . ($q ? ('?' . $q) : '');
    }

    // Generate cover image URL and show it immediately in the preview
    public function generateImages(?string $prompt = null, array $opts = []): void
    {
        $prompt = trim($prompt ?: $this->title ?: 'high quality blog cover, soft lighting, clean background, cinematic');
        if ($prompt === '') {
            $this->addError('cover_url', 'Enter a title or prompt first.');
            return;
        }

        $url = $this->pollinationsUrl($prompt, $opts);

        // Optionally pre‑warm the image (catch errors early). Pollinations is keyless.
        try {
            Http::timeout(90)->accept('*/*')->get($url)->throw();
            $this->cover_url = $url; // Blade will preview this URL
        } catch (\Throwable $e) {
            $this->addError('cover_url', 'Image generation failed. Try a different prompt.');
        }
    }



    // php
    public function generateFullPost(string $key = 'POLLINATIONS_API_TOKEN'): void
    {
        $this->validate(['title' => 'required|min:5']);

        $apiKey = env($key);
        $usingOpenRouter = $apiKey == 'OPENROUTER_API_KEY';
        $url = $usingOpenRouter
            ? 'https://openrouter.ai/api/v1/chat/completions'
            : 'https://text.pollinations.ai/openai';
        $headers = $usingOpenRouter ? ['Authorization' => 'Bearer ' . $apiKey] : [];

        $categoriesPayload = collect($this->categories)
            ->map(fn($c) => ['id' => $c->id, 'name' => $c->name])
            ->values()
            ->all();

        $messages = [
            [
                'role' => 'system',
                'content' => 'You are a strict JSON content generator. Return ONLY valid JSON with this exact schema:
{
  "excerpt": "1-2 sentence summary in Bangla",
  "content_markdown": "Full blog post in Bangla using Markdown (## headings, ### subheadings, lists, tips, conclusion). Avoid very long intro.",
  "tags": ["max 6 short tags in Bangla or English"],
  "cover_prompt": "A concise English prompt for image generation (no people, no text).",
  "category": {"id": null | number, "name": null | string}
}
No extra text before or after the JSON.',
            ],
            [
                'role' => 'user',
                'content' => json_encode([
                    'title' => $this->title,
                    'categories' => $categoriesPayload,
                    'length' => '700-1200 words',
                    'tone' => 'helpful, clear, engaging',
                    'audience' => 'general readers',
                ]),
            ],
        ];

        try {
            $resp = \Illuminate\Support\Facades\Http::withHeaders($headers)
                ->timeout(90)
                ->post($url, [
                    'model' => $usingOpenRouter ? 'openai/gpt-oss-20b:free' : 'openai',
                    'temperature' => 0.7,
                    'messages' => $messages,
                ])
                ->throw();

            $raw = data_get($resp->json(), 'choices.0.message.content', '');
            if (!preg_match('/\{.*\}/s', (string) $raw, $m)) {
                $this->alert('error', 'AI did not return JSON.');
                return;
            }

            $data = json_decode($m[0], true);
            if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
                $this->alert('error', 'Invalid JSON from AI.');
                return;
            }

            $excerpt = (string) data_get($data, 'excerpt', '');
            $contentMd = (string) data_get($data, 'content_markdown', '');
            $tagsArr = (array) data_get($data, 'tags', []);
            $coverPrompt = (string) data_get($data, 'cover_prompt', $this->title);
            $catIdAi = data_get($data, 'category.id');
            $catNameAi = data_get($data, 'category.name');

            if ($contentMd === '') {
                $this->alert('error', 'AI did not generate content.');
                return;
            }

            // Convert Markdown -> HTML
            $converter = new \League\CommonMark\CommonMarkConverter();
            $this->content = (string) $converter->convert($contentMd);
            $this->excerpt = \Illuminate\Support\Str::limit(strip_tags($excerpt ?: $this->content), 255);
            $this->meta_title = $this->title;
            $this->meta_description = \Illuminate\Support\Str::limit(strip_tags($this->excerpt), 155);
            $this->tags = collect($tagsArr)->map(fn($t) => trim((string) $t))->filter()->unique()->take(8)->implode(', ');

            // Category mapping: id -> name -> keyword fallback
            $cats = collect($this->categories);
            $chosen = null;
            if (is_numeric($catIdAi)) {
                $chosen = $cats->firstWhere('id', (int) $catIdAi);
            }
            if (!$chosen && $catNameAi) {
                $chosen = $cats->first(fn($c) => strcasecmp($c->name, (string) $catNameAi) === 0);
            }
            if (!$chosen) {
                $lc = mb_strtolower(strip_tags($this->content));
                $chosen = $cats->first(fn($c) => mb_strpos($lc, mb_strtolower($c->name)) !== false);
            }
            if ($chosen) {
                $this->category_id = $chosen->id;
            }

            // Build Pollinations cover URL (keyless)
            $this->cover_url = $this->pollinationsUrl($coverPrompt, [
                'model' => 'flux-realism',
                'width' => 1280,
                'height' => 720,
                'seed' => 420,
                'nologo' => 'true',
            ]);
            $this->url_title = $coverPrompt;

            $this->alert('success', __('Post drafted by AI.'));
            $this->save();
        } catch (\Throwable $e) {
            $this->alert('error', 'Failed to generate post.');
        }
    }

    public function prothomAlo()
    {
        ScrapeProthomAloJob::dispatch();

    }
    public function jamunaTv()
    {
        ScrapeJamunaTvJob::dispatch();

    }
    public function banglaNews24()
    {
        ScrapeNews24Job::dispatch();

    }
    public function jagoNews()
    {
        ScrapeJagoNewsJob::dispatch();

    }
    public function banglaTribune()
    {
        ScrapeBanglaTribuneJob::dispatch();

    }
    public function newsDetails($url)
    {

        $client = new \GuzzleHttp\Client();
        $res = $client->get($url,  [
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                'Accept'     => 'text/html,application/xhtml+xml',
            ]
        ]);
        $html = (string) $res->getBody();

        $crawler = new Crawler($html);
        if ($crawler->filter('h1.IiRps')->count() > 0) {
            // Get title
            $title = $crawler->filter('h1.IiRps')->first()->text();

            // Get content
            $contentNodes = $crawler->filter('div.story-content p');
            $content = '';
            foreach ($contentNodes as $p) {
                $content .= '<p>' . $p->textContent . '</p>';
            }

            // Get image (first image)
            $imageNode = $crawler->filter('div.qt-image-dynamic img')->first();
            $image = null;
            if ($crawler->filter('meta[property="og:image"]')->count()) {
                $image = $crawler->filter('meta[property="og:image"]')->attr('content');
            } elseif ($crawler->filter('meta[name="twitter:image"]')->count()) {
                $image = $crawler->filter('meta[name="twitter:image"]')->attr('content');
            }

            // Get category
            $categoryName = $crawler->filter('a.vXi2j')->first()->text();
            $parent = Category::firstOrCreate(['name' => 'Prothomalo']);
            $category = Category::firstOrCreate(['name' => $categoryName, 'parent_id' => $parent->id]);

            // Published date
            $published_at = $crawler->filter('time')->first()->attr('datetime');

            // Save post
            $post = Post::create([
                'user_id' => 1, // just demo, use actual author id
                'category_id' => $category->id,
                'title' => $title,
                'slug' => Str::slug($title),
                'content' => $content,
                'image' => $image,
                'source' => $url,
                'excerpt' => Str::limit(strip_tags($content), 200),
                'tags' => json_encode([]),
                'views' => 0,
                'status' => 'published',
                'type' => 'normal',
                'meta_title' => $title,
                'meta_description' => Str::limit(strip_tags($content), 160),
                'published_at' => $published_at,
            ]);

//            $this->alert('success', __('Post created from Prothomalo .' . $post->id));
        }
    }

}

