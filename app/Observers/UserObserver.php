<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\User;
use Illuminate\Support\Facades\Cache;

final class UserObserver
{
    public function created(User $user): void
    {
        $this->clearUserListCache();
    }

    public function updated(User $user): void
    {
        $this->clearUserListCache();
    }

    public function deleted(User $user): void
    {
        $this->clearUserListCache();
    }

    public function restored(User $user): void
    {
        $this->clearUserListCache();
    }

    public function forceDeleted(User $user): void
    {
        $this->clearUserListCache();
    }

    private function clearUserListCache(): void
    {
        Cache::tags(['users_index'])->flush();
    }
}
