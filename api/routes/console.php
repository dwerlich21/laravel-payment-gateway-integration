<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

// Definir DISPLAY vazio para evitar erros de X11 do Puppeteer/Chromium
putenv('DISPLAY=');

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Scheduled Tasks
|--------------------------------------------------------------------------
*/

// Check goal deadlines daily at 8:00 AM
Schedule::command('goals:check-deadlines --days=3')->dailyAt('08:00');
Schedule::command('goals:check-deadlines --days=1')->dailyAt('08:00');
