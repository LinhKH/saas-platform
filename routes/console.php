<?php

use Illuminate\Support\Facades\Schedule;
use App\Jobs\RenewSubscriptionsJob;

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::job(new RenewSubscriptionsJob)
    ->everyMinute();