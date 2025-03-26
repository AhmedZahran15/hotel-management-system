<?php

use App\Console\Commands\LoginReminderCommand;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule the login reminder command to run daily
Schedule::command(LoginReminderCommand::class)->daily();
