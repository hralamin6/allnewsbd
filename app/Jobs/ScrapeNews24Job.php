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

class ScrapeNews24Job implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $collectionUrl;

    public function __construct($collectionUrl = 'https://www.banglanews24.com/')
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
            $crawler->filter('#latest ul li a')->each(function ($node) {
                $link = $node->attr('href');
                if (!$link || Post::where('source', $link)->exists()) {
                    return;
                }
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
            $title = $crawler->filter('div.post-heading h1')->count()
                ? $crawler->filter('div.post-heading h1')->text()
                : ($crawler->filter('title')->count() ? $crawler->filter('title')->text() : 'No Title');

            $content = '';
            $crawler->filter('div.news-article article p')->each(function ($p) use (&$content) {
                $content .= '<p>' . trim($p->text()) . '</p>';
            });
            $image = null;
            if ($crawler->filter('meta[property="og:image"]')->count()) {
                $image = $crawler->filter('meta[property="og:image"]')->attr('content');
            }elseif ($crawler->filter('div.main-img img')->count()) {
                $image = $crawler->filter('div.main-img img')->attr('src');
            }
            $categoryName = $crawler->filter('div.container h1')->count()
                ? $crawler->filter('div.container h1')->text()
                : 'Banglanews24 Others';

            $parent = Category::firstOrCreate(['name' => 'Banglanews24']);
            $category = Category::firstOrCreate([
                'name' => $categoryName,
                'parent_id' => $parent->id
            ]);

            $published_at = now();
            if ($crawler->filter('spam.time')->count()) {
                $publishedText = $crawler->filter('spam.time')->text(); // যেমন: "আপডেট: ১৭:৫৬, সেপ্টেম্বর ১১, ২০২৫"
                $publishedText = str_replace(['আপডেট:', ','], '', $publishedText);
                $publishedText = trim($publishedText);
                $publishedText = preg_replace('/(\d+)(st|nd|rd|th)/', '$1', $publishedText);
                $published_at = $this->bn2enNumber($publishedText);
                $published_at = $this->bnMonth2en($published_at);
                $published_at = Carbon::parse($published_at);
            }

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
   public function bn2enNumber($bnNumber) {
        $bn = ['০','১','২','৩','৪','৫','৬','৭','৮','৯'];
        $en = ['0','1','2','3','4','5','6','7','8','9'];
        return str_replace($bn, $en, $bnNumber);
    }

   public function bnMonth2en($text) {
        $months = [
            'জানুয়ারি'=>'January',
            'ফেব্রুয়ারি'=>'February',
            'মার্চ'=>'March',
            'এপ্রিল'=>'April',
            'মে'=>'May',
            'জুন'=>'June',
            'জুলাই'=>'July',
            'আগস্ট'=>'August',
            'সেপ্টেম্বর'=>'September',
            'অক্টোবর'=>'October',
            'নভেম্বর'=>'November',
            'ডিসেম্বর'=>'December'
        ];
        return strtr($text, $months);
    }
}
