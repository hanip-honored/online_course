<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule recommendation model training
Schedule::command('recommender:train')
    ->hourly()
    ->withoutOverlapping()
    ->runInBackground()
    ->onSuccess(function () {
        \Log::info('Recommender model trained successfully via schedule');
    })
    ->onFailure(function () {
        \Log::error('Scheduled recommender training failed');
    });
