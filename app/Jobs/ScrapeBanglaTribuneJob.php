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

class ScrapeBanglaTribuneJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $collectionUrl;

    public function __construct($collectionUrl = 'https://www.banglatribune.com/%E0%A6%86%E0%A6%9C%E0%A6%95%E0%A7%87%E0%A6%B0-%E0%A6%96%E0%A6%AC%E0%A6%B0')
    {
        $this->collectionUrl = $collectionUrl;
    }

    public function handle()
    {
        try {
            $client = new Client([
                'timeout' => 300,
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

            $crawler->filter('a.link_overlay')->each(function ($node) use ($client) {
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

            $title = $crawler->filter('div.col_in h1.title')->count()
                ? trim($crawler->filter('div.col_in h1.title')->text())
                : ($crawler->filter('title')->count() ? trim($crawler->filter('title')->text()) : 'No Title');

            $content = '';
            $crawler->filter('article.jw_detail_content_holder .jw_article_body p')->each(function ($p) use (&$content) {
                $content .= '<p>' . trim($p->text()) . '</p>';
            });

            $image = null;
            if ($crawler->filter('meta[property="og:image"]')->count()) {
                $image = $crawler->filter('meta[property="og:image"]')->attr('content');
            } elseif ($crawler->filter('div.featured_image img')->count()) {
                $image = $crawler->filter('div.featured_image img')->attr('src');
            }

            $categoryName = $crawler->filter('a[itemprop="item"] strong[itemprop="name"]')->count()
                ? trim($crawler->filter('a[itemprop="item"] strong[itemprop="name"]')->last()->text())
                : 'BanglaTribune Others';

            $parent = Category::firstOrCreate(['name' => 'BanglaTribune']);
            $category = Category::firstOrCreate([
                'name' => $categoryName,
                'parent_id' => $parent->id
            ]);



            $published_at = now();
            if ($crawler->filter('div.detail_holder .tts_time')->count()) {
                $publishedText = $crawler->filter('div.detail_holder .tts_time')->attr('content'); // ISO format: 2025-09-12T13:00:23+06:00
                $published_at = Carbon::parse($publishedText);
            }


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
