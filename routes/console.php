<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule overdue loans update
Schedule::command('loans:update-overdue')->hourly();
Schedule::command('loans:update-overdue')->dailyAt('00:01');
