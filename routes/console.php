<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('alerts:generate')->dailyAt('09:30');
Schedule::command('alerts:license-reminders')->dailyAt('09:35');
Schedule::command('alerts:license-reminders')->dailyAt('13:00');
Schedule::command('alerts:license-reminders')->dailyAt('17:00');
Schedule::command('alerts:vehicle-document-reminders')->dailyAt('09:35');
Schedule::command('alerts:vehicle-document-reminders')->dailyAt('13:00');
Schedule::command('alerts:vehicle-document-reminders')->dailyAt('17:00');
Schedule::command('reports:send-monthly')->monthlyOn(1, '07:00');
