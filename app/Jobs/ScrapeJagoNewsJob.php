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
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Support\Facades\Log;

class ScrapeJagoNewsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $collectionUrl;

    public function __construct($collectionUrl = 'https://www.jagonews24.com/archive')
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
            $crawler->filter('div.paddingTop10, div.col-sm-8')->each(function ($node) {
                $link = $node->filter('h3 a')->attr('href');
//                $date = "১১:৩৭ এএম, ১২ সেপ্টেম্বর ২০২৫, শুক্রবার";
                $date = $node->filter('small')->text();
                if (!$link || Post::where('source', $link)->exists()) {
                    return;
                }
                $this->scrapeSingle($link, $date);
            });
        } catch (\Exception $e) {
            Log::error("Error scraping collection {$this->collectionUrl}: " . $e->getMessage());
        }
    }

    public function scrapeSingle($link, $date = null)
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

            $title = $crawler->filter('h1.no-margin')->count()
                ? $crawler->filter('h1.no-margin')->text()
                : ($crawler->filter('title')->count() ? $crawler->filter('title')->text() : 'No Title');

            $content = '';
            $crawler->filter('div.content-details p')->each(function ($p) use (&$content) {
                $content .= '<p>' . trim($p->text()) . '</p>';
            });

            $image = null;
         if($crawler->filter('div.featured-image img')->count()) {
                $image = $crawler->filter('div.featured-image img')->attr('src');
            }elseif($crawler->filter('meta[property="og:image"]')->count()) {
             $image = $crawler->filter('meta[property="og:image"]')->attr('content');
         }

            $categoryName = $crawler->filter('ol.breadcrumb li a')->count()
                ? $crawler->filter('ol.breadcrumb li a')->last()->text()
                : 'Jagonews Others';

            $parent = Category::firstOrCreate(['name' => 'Jagonews']);
            $category = Category::firstOrCreate([
                'name' => $categoryName,
                'parent_id' => $parent->id
            ]);

                $publishedText = $date;
                $publishedText = preg_replace('/,?\s*(শনিবার|রবিবার|সোমবার|মঙ্গলবার|বুধবার|বৃহস্পতিবার|শুক্রবার|শনি)/u', '', $publishedText);                $publishedText = trim($publishedText);
                $publishedText = $this->bn2enNumber($publishedText);
                $publishedText = $this->bnMeridiem2en($publishedText);
                $publishedText = $this->bnMonth2en($publishedText);
                $publishedText = trim(str_replace(',', '', $publishedText));
                $published_at = Carbon::parse($publishedText);



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

            $client = new Client([
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/117.0 Safari/537.36',
                    'Referer' => 'https://www.jagonews24.com/',
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                    'Accept-Language' => 'en-US,en;q=0.5',
                ],
            ]);

            try {
                $res = $client->get($image, ['verify' => false]); // HTTPS cert skip
                if ($res->getStatusCode() === 200) {
                    $tempFile = tempnam(sys_get_temp_dir(), 'img');
                    file_put_contents($tempFile, $res->getBody());

                    $extension = pathinfo(parse_url($image, PHP_URL_PATH), PATHINFO_EXTENSION);
                   $media = $post->addMedia($tempFile)
                        ->usingFileName($post->id . '.' . $extension)
                        ->toMediaCollection('postImages');
                    $path = storage_path("app/public/Post/".$media->id.'/'. $media->file_name);
                    if (file_exists($path)) {
                        unlink($path);
                    }
                    unlink($tempFile);
                } else {
                    \Log::error("Image not reachable: $image, Status: ".$res->getStatusCode());
                }
            } catch (\Exception $e) {
                \Log::error("Error fetching image: ".$e->getMessage());
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
    public function bnMeridiem2en($text) {
        // Normalize common Bangla AM/PM variants to English AM/PM
        $map = [
            'এএম' => 'AM',
            'এ.এম.' => 'AM',
            'এ এম' => 'AM',
            'এ. এম.' => 'AM',
            'পিএম' => 'PM',
            'পি.এম.' => 'PM',
            'পি এম' => 'PM',
            'পি. এম.' => 'PM',
        ];
        $text = preg_replace('/\s+/u', ' ', $text);
        return strtr($text, $map);
    }
}
