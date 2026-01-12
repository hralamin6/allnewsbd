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

class ScrapeJamunaTvJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $collectionUrl;

    public function __construct($collectionUrl = 'https://jamuna.tv/')
    {
        $this->collectionUrl = $collectionUrl;
    }

    public function handle()
    {
        try {
            $client = new Client([
                'timeout' => 300,
                'cookies' => true,
                'verify'  => true,
                'headers' => [
                    'User-Agent' =>
                        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) ' .
                        'AppleWebKit/537.36 (KHTML, like Gecko) ' .
                        'Chrome/121.0.0.0 Safari/537.36',
                    'Accept' =>
                        'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
                    'Accept-Language' => 'en-US,en;q=0.9,bn;q=0.8',
                    'Accept-Encoding' => 'gzip, deflate, br',
                    'Referer' => 'https://www.google.com/',
                    'Connection' => 'keep-alive',
                    'Upgrade-Insecure-Requests' => '1',
                ],
                'curl' => [
                    CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                ],
            ]);

            $response = $client->get($this->collectionUrl);
            $html = (string)$response->getBody();

            $crawler = new Crawler($html);

            $crawler->filter('a.story-title-link')->each(function ($node) use ($client) {
                $link = $node->attr('href');
                if (!$link || Post::where('source', $link)->exists()) return;
                $this->scrapeSingle($link);
            });
            $crawler->filter('h5.entry-title a')->slice(0, 8)->each(function ($node) use ($client) {
                $link = $node->attr('href');
                if (!$link || Post::where('source', $link)->exists()) return;
                $this->scrapeSingle($link);
            });
        } catch (\Exception $e) {
            Log::error("Error scraping collection {$this->collectionUrl}: " . $e->getMessage());
        }
    }

    public function scrapeSingle($link)
    {
        try {
            $client = new Client([
                'timeout' => 300,
                'verify'  => true,
                'headers' => ['User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'],
                'curl'    => [CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2],
            ]);

            $res = $client->get($link);
            $html = (string)$res->getBody();
            $crawler = new Crawler($html);

            // Title
            $title = $crawler->filter('h1.story-title')->count()
                ? $crawler->filter('h1.story-title')->text()
                : ($crawler->filter('title')->count() ? $crawler->filter('title')->text() : 'No Title');

            $content = '';
            $crawler->filter('div.article-content p')->each(function ($p) use (&$content) {
                $content .= '<p>' . $p->text() . '</p>';
            });

            $image = null;
            if ($crawler->filter('meta[property="og:image"]')->count()) {
                $image = $crawler->filter('meta[property="og:image"]')->attr('content');
            } elseif ($crawler->filter('img.wp-post-image')->count()) {
                $image = $crawler->filter('img.wp-post-image')->attr('src');
            }

            $categoryName = $crawler->filter('a.story-meta-item-link')->count()
                ? $crawler->filter('a.story-meta-item-link')->text()
                : 'JamunaTV others';
            $parent = Category::firstOrCreate(['name' => 'JamunaTV']);
            $category = Category::firstOrCreate(['name' => $categoryName, 'parent_id' => $parent->id]);

            $published_at = $crawler->filter('time')->count()
                ? $crawler->filter('time')->text()
                : now();
            $published_at = str_replace(',', '', $published_at);
            $cleanDate = preg_replace('/(\d+)(st|nd|rd|th)/', '$1', $published_at);
            $published_at = Carbon::parse($cleanDate);

            // Save post
          $post =  Post::create([
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
    }
}
