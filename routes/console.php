<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('queue:work --stop-when-empty')->everyMinute();
Schedule::command('pedidos:limpar-abandonados')->hourly();
Schedule::command('lojas:gerenciar-horarios')->everyFiveMinutes();
Schedule::command('planos:gerenciar')->dailyAt('09:00');
