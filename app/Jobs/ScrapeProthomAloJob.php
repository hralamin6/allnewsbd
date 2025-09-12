<?php

namespace App\Jobs;

use App\Models\Category;
use App\Models\Post;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use GuzzleHttp\Client;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Support\Facades\Log;

class ScrapeProthomAloJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $collectionUrl;

    public function __construct($collectionUrl = 'https://www.prothomalo.com/collection/latest')
    {
        $this->collectionUrl = $collectionUrl;
    }

    public function handle()
    {
        try {
            $client = new Client([
                'timeout' => 300,
                'verify'  => true,
                'headers' => ['User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'],
                'curl'    => [CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2],
            ]);

            $response = $client->get($this->collectionUrl);
            $html = (string)$response->getBody();

            $crawler = new Crawler($html);

            $crawler->filter('h3.headline-title a')->each(function ($node) use ($client) {
                $link = $node->attr('href');

                if (!$link || Post::where('source', $link)->exists()) return;

                try {
                    $res = $client->get($link);
                    $html = (string)$res->getBody();
                    $crawler = new Crawler($html);

                    // Title
                    $title = $crawler->filter('h1.IiRps')->count()
                        ? $crawler->filter('h1.IiRps')->text()
                        : ($crawler->filter('title')->count() ? $crawler->filter('title')->text() : 'No Title');
                    // Content
                    $content = '';
                    $crawler->filter('div.story-content p')->each(function ($p) use (&$content) {
                        $content .= '<p>' . $p->text() . '</p>';
                    });
                    $crawler->filter('div.Ibzfy p')->each(function ($p) use (&$content) {
                        $content .= '<p>' . $p->text() . '</p>';
                    });
                    $crawler->filter('div.iHgWl p')->each(function ($p) use (&$content) {
                        $content .= '<p>' . $p->text() . '</p>';
                    });

                    // Image
                    $image = null;
                    if ($crawler->filter('meta[property="og:image"]')->count()) {
                        $image = $crawler->filter('meta[property="og:image"]')->attr('content');
                    } elseif ($crawler->filter('meta[name="twitter:image"]')->count()) {
                        $image = $crawler->filter('meta[name="twitter:image"]')->attr('content');
                    }

                    // Category
                    $categoryName = $crawler->filter('a.vXi2j')->count() ? $crawler->filter('a.vXi2j')->text() : 'Prothomalo';
                    $parent = Category::firstOrCreate(['name' => 'Prothomalo']);
                    $category = Category::firstOrCreate(['name' => $categoryName, 'parent_id' => $parent->id]);

                    // Published date
                    $published_at = $crawler->filter('time')->count() ? $crawler->filter('time')->attr('datetime') : now();
//                    $published_at = Carbon::parse($published_at);
                    // Save post
                    $post = Post::create([
                        'user_id' => 1,
                        'category_id' => $category->id,
                        'title' => $title,
                        'slug' => Str::slug($title),
                        'content' => $content,
                        'image' => $image,
                        'source' => $link,
                        'excerpt' => Str::limit(strip_tags($content), 200),
                        'tags' => json_encode([]),
                        'views' => 0,
                        'status' => 'published',
                        'type' => 'featured',
                        'meta_title' => $title,
                        'meta_description' => Str::limit(strip_tags($content), 160),
                        'published_at' => $published_at,
                    ]);
                    $extension = pathinfo(parse_url($image, PHP_URL_PATH), PATHINFO_EXTENSION);
                    $media =  $post->addMediaFromUrl($image)->usingFileName($post->id. '.' . $extension)->toMediaCollection('postImages');
                    $path = storage_path("app/public/Post/".$media->id.'/'. $media->file_name);
                    if (file_exists($path)) {
                        unlink($path);
                    }
                } catch (\Exception $e) {
                    Log::error("Error scraping post $link: " . $e->getMessage());
                }
            });

        } catch (\Exception $e) {
            Log::error("Error scraping collection {$this->collectionUrl}: " . $e->getMessage());
        }
    }
}
