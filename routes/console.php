<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Process queued jobs every minute (cPanel compatible — no persistent worker needed)
// Add this cron in cPanel: * * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
Schedule::command('queue:work --stop-when-empty --timeout=7200 --tries=1')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground();

