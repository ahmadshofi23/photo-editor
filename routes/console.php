<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Auto-cleanup: hapus gambar & file original yang lebih dari 30 hari, setiap hari jam 02:00
Schedule::command('image:cleanup')->dailyAt('02:00');
