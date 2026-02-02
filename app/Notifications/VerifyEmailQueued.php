<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

final class VerifyEmailQueued extends VerifyEmail implements ShouldQueue
{
    use Queueable;

    public $tries = 3;

    public $backoff = [10, 60];
}
