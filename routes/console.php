<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('app:update-items-quantity')->timezone('Africa/Nairobi')->everyFiveMinutes();

Schedule::command('app:update-stock-levels-quantity')->timezone('Africa/Nairobi')->everyFifteenMinutes();

Schedule::command('app:item-categories:update-items-count')->timezone('Africa/Nairobi')->hourly();
