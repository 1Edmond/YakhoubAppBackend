<?php

use Illuminate\Support\Facades\Schedule;
Schedule::command('transfer:reset-daily')->dailyAt('00:00')->timezone(config('multishop.transfer_timezone', 'Africa/Ndjamena'));
