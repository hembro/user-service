<?php

declare(strict_types=1);

use App\Models\OutboxEvent;
use Illuminate\Support\Facades\Schedule;

Schedule::command('passport:purge')->monthly();

// Fallback command
Schedule::command('events:publish')
    ->everyMinute()
    ->withoutOverlapping(); // Prevent race conditions

Schedule::command('model:prune', ['--model' => OutboxEvent::class])
    ->daily();
