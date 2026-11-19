<?php

use Illuminate\Support\Facades\Schedule;

// Keeps the countries/cities cache warm so users never wait for it
Schedule::command('duffel:warm-places')->dailyAt('03:00')->withoutOverlapping();
