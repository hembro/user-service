<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\UserCreated;

final class PublishUserCreationToBroker
{
    public function __construct() {}

    public function handle(UserCreated $event): void {}
}
