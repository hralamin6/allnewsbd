<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Jobs\GenerateHourlyPostJob;
use App\Jobs\ScrapeNews24Job;
use App\Jobs\ScrapeProthomAloJob;
use App\Jobs\ScrapeJamunaTvJob;
use App\Jobs\ScrapeBanglaTribuneJob;
use App\Jobs\ScrapeJagoNewsJob;

// Schedule news scraping jobs one-by-one every 10 minutes across the hour
Schedule::job(new ScrapeNews24Job())
    ->name('scrape-news24')
    ->withoutOverlapping()
    ->cron('0 * * * *');
Schedule::job(new ScrapeNews24Job())
    ->name('scrape-news24')
    ->withoutOverlapping()
    ->cron('30 * * * *');


Schedule::job(new ScrapeProthomAloJob())
    ->name('scrape-prothomalo')
    ->withoutOverlapping()
    ->cron('10 * * * *');
Schedule::job(new ScrapeProthomAloJob())
    ->name('scrape-prothomalo')
    ->withoutOverlapping()
    ->cron('25 * * * *');
Schedule::job(new ScrapeProthomAloJob())
    ->name('scrape-prothomalo')
    ->withoutOverlapping()
    ->cron('40 * * * *');
Schedule::job(new ScrapeProthomAloJob())
    ->name('scrape-prothomalo')
    ->withoutOverlapping()
    ->cron('55 * * * *');


Schedule::job(new ScrapeBanglaTribuneJob())
    ->name('scrape-bangla-tribune')
    ->withoutOverlapping()
    ->cron('5 * * * *');
Schedule::job(new ScrapeBanglaTribuneJob())
    ->name('scrape-bangla-tribune')
    ->withoutOverlapping()
    ->cron('20 * * * *');
Schedule::job(new ScrapeBanglaTribuneJob())
    ->name('scrape-bangla-tribune')
    ->withoutOverlapping()
    ->cron('50 * * * *');


Schedule::job(new ScrapeJamunaTvJob())
    ->name('scrape-jamuna-tv')
    ->withoutOverlapping()
    ->cron('7 * * * *');
Schedule::job(new ScrapeJamunaTvJob())
    ->name('scrape-jamuna-tv')
    ->withoutOverlapping()
    ->cron('27 * * * *');
Schedule::job(new ScrapeJamunaTvJob())
    ->name('scrape-jamuna-tv')
    ->withoutOverlapping()
    ->cron('57 * * * *');

Schedule::job(new ScrapeJagoNewsJob())
    ->name('scrape-jagonews')
    ->withoutOverlapping()
    ->cron('37 * * * *');
Schedule::job(new ScrapeJagoNewsJob())
    ->name('scrape-jagonews')
    ->withoutOverlapping()
    ->cron('47 * * * *');
Schedule::job(new ScrapeJagoNewsJob())
    ->name('scrape-jagonews')
    ->withoutOverlapping()
    ->cron('12 * * * *');

// If you want something at :50, add it here (e.g., another scraper or a summarizer)

//Schedule::job(new \App\Jobs\GenerateHourlyPostJob())->everyMinute();
