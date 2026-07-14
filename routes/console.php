<?php

use Illuminate\Support\Facades\Schedule;
use App\Console\Commands\AutoCancelStaleWaste;

Schedule::command(AutoCancelStaleWaste::class)->daily();
